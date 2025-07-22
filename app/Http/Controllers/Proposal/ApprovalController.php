<?php

namespace App\Http\Controllers\Proposal;

use App\Models\User;
use App\Models\Jurusan;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Helper\CrudController;
use App\Traits\ApprovalProposalRequestValidator;
use App\Http\Controllers\Notifikasi\NotifikasiController;

class ApprovalController extends Controller
{
    use ApprovalProposalRequestValidator;
    public function index()
    {
        $head = ['No Proposal', 'Nama', 'Ketua Pelaksana', 'Organisasi'];
        $admin = Gate::allows('admin');
        if ($admin) {
            $head[] = 'Dosen';
            $head[] = 'Jurusan';
        }

        $head[] = 'Status';
        $head[] = 'Aksi';
        return view('Pages.Proposal.approval', compact('head'));
    }

    public function edit(Request $request, $id)
    {
        $data = $this->validateApprovalProposalEligible($request, true);
        $valid = $this->showApprovalEligible($data);
        if (!$valid) {
            abort(404);
        }
        $data = [
            'id' => encrypt($data->id),
            'ketua_id' => $data->mahasiswa_id,
            'name' => $data->name,
            'no_proposal' => $data->no_proposal,
            'desc' => $data->desc,
            'organisasi' => $data->user->userable->name,
            'dosen' => $data->dosen->name,
            'file_url' => Storage::temporaryUrl($data->file, now()->addMinutes(5)),
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'status' => $data->status,
            'mahasiswa' => $data->mahasiswa,
            'approvalBtn' => $this->approvalBtnEligible($data),
            'approvalUrl' => $this->urlProposalEligible($data),
        ];
        return view('Pages.Proposal.validasi', compact('data'));
    }

    public function accProposal($proposal, $approve, $field, $status, $desc)
    {
        $dataField = [
            $field => $approve,
            'status' => $status,
        ];
        $Crud = new CrudController(Proposal::class, data: $proposal, id: $proposal->id, dataField: $dataField, description: $desc, content: 'Approval Proposal');
        $data = $Crud->updateWithReturnData();

        return $data;
    }

    public function rejectProposal($proposal, $approve, $field, $reason, $status, $desc)
    {
        $dataField = [
            'is_acc_dosen' => $approve,
            'is_acc_kaprodi' => $approve,
            'is_acc_minat_bakat' => $approve,
            'is_acc_layanan' => $approve,
            'is_acc_wakil_rektor' => $approve,
            'status' => 'Rejected',
            'alasan_tolak' => $reason
        ];
        $Crud = new CrudController(Proposal::class, data: $proposal, dataField: $dataField, description: $desc, content: 'Approval Proposal');
        $data = $Crud->updateWithReturnData();

        $notifikasi = new NotifikasiController();
        $mahasiswa = $data->ketua;
        $noHp = $mahasiswa->no_hp;

        $message = $notifikasi->generateMessageForRejected(jenisPengajuan: 'Proposal', alasanTolak: $reason, judulKegiatan: $data->name, unitKemahasiswaan: $data->user->userable->name, route: route('proposal.edit', encrypt($data->id)));
        $target = 'Ketua Pelaksana';
        $messageFor = 'Ditolak';
        $response = $notifikasi->sendMessage($noHp, $message, $target, $messageFor);

        return $response;
    }

    public function approvalDosen(Request $request)
    {
        $request->validate([
            'reason' => [
                Rule::requiredIf($request->boolean('approve') == false),
            ],
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        try {
            $approve = $request->boolean('approve');
            DB::beginTransaction();
            $data = $this->validateApprovalProposalEligible($request);
            $this->approvalDosenEligible($data);
            $nonJurusan = $this->checkNonJurusan($data);
            $status = $nonJurusan ? 'Pending Layanan Mahasiswa' : 'Pending Kaprodi';
            $notifikasi = new NotifikasiController();
            if ($approve) {
                $data =  $this->accProposal($data, $approve, 'is_acc_dosen', $status, 'Dosen Telah Menyetujui Proposal Anda!');
                $noProposal = explode('/', $data->no_proposal);
                $kodeJurusan = $noProposal[1];

                if ($nonJurusan) {
                    $response = 'Pengajuan Berhasil Diterima';
                } else {
                    $jurusan = Jurusan::select(['id'])->where('kode', $kodeJurusan)->first();
                    $kaprodi = $this->getKaprodi($jurusan->id);
                    $noHp =  $kaprodi ? $kaprodi->no_hp : '0';
                    $message = $kaprodi ? $notifikasi->generateMessageForVerifikator(jenisPengajuan: 'Proposal', nama: $kaprodi->name, judulKegiatan: $data->name, unitKemahasiswaan: $data->user->userable->name, route: route('approval-proposal.edit', encrypt($data->id))) : '-';
                    $target = $nonJurusan ? 'Layanan Mahasiswa' : 'Kaprodi';
                    $messageFor = 'Pengajuan';
                    $response = $notifikasi->sendMessage($noHp, $message, $target, $messageFor);
                }
            } else {
                $response =  $this->rejectProposal($data, $approve, null, $request->reason, '', 'Dosen Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
            }
            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $response,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function approvalKaprodi(Request $request)
    {
        $request->validate([
            'reason' => [
                Rule::requiredIf($request->boolean('approve') == false),
            ],
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        try {
            $approve = $request->boolean('approve');
            DB::beginTransaction();
            $data = $this->validateApprovalProposalEligible($request);
            $this->approvalKaprodiEligible($data);
            if ($approve) {
                $data =  $this->accProposal($data, $approve, 'is_acc_kaprodi', 'Pending Minat dan Bakat', 'Kaprodi Telah Menyetujui Proposal Anda!');
                $response = 'Pengajuan Berhasil Diterima';
            } else {
                $response =  $this->rejectProposal($data, $approve, null, $request->reason, '', 'Kaprodi Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $response,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function approvalMinatBakat(Request $request)
    {
        $request->validate([
            'reason' => [
                Rule::requiredIf($request->boolean('approve') == false),
            ],
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        try {
            $approve = $request->boolean('approve');
            DB::beginTransaction();
            $data = $this->validateApprovalProposalEligible($request);
            $this->approvalMinatBakatEligible($data);
            if ($approve) {
                $data =  $this->accProposal($data, $approve, 'is_acc_minat_bakat', 'Pending Layanan Mahasiswa', 'Kepala Bagian Minat dan Bakat Telah Menyetujui Proposal Anda!');
                $response = 'Pengajuan Berhasil Diterima';
            } else {
                $response =  $this->rejectProposal($data, $approve, null, $request->reason, '', 'Kepala Bagian Minat dan Bakat Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $response,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function approvalLayananMahasiswa(Request $request)
    {
        $request->validate([
            'reason' => [
                Rule::requiredIf($request->boolean('approve') == false),
            ],
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        try {
            $approve = $request->boolean('approve');
            DB::beginTransaction();
            $data = $this->validateApprovalProposalEligible($request);
            $this->approvalLayananMahasiswaEligible($data);
            if ($approve) {
                $data =  $this->accProposal($data, $approve, 'is_acc_layanan', 'Pending Wakil Rektor', 'Layanan Mahasiswa Menyetujui Proposal Anda!');
                $response = 'Pengajuan Berhasil Diterima';
            } else {
                $response =  $this->rejectProposal($data, $approve, null, $request->reason, '', 'Layanan Mahasiswa Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $response,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function approvalWakilRektor(Request $request)
    {
        $request->validate([
            'reason' => [
                Rule::requiredIf($request->boolean('approve') == false),
            ],
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
        ]);

        try {
            $approve = $request->boolean('approve');
            DB::beginTransaction();
            $data = $this->validateApprovalProposalEligible($request);
            $this->approvalWakilRektorEligible($data);
            $notifikasi = new NotifikasiController();
            if ($approve) {
                $data =  $this->accProposal($data, $approve, 'is_acc_wakil_rektor', 'Accepted', 'Wakil Rektor 1 Menyetujui Proposal Anda!');
                $message = $notifikasi->generateMessageForAccepted(jenisPengajuan: 'Proposal', judulKegiatan: $data->name, unitKemahasiswaan: $data->user->userable->name);
                $messageFor = 'Pengajuan';
                $mahasiswa = $data->ketua;
                $noHp = $mahasiswa->no_hp;
                $target = 'Ketua Pelaksana';
                $response = $notifikasi->sendMessage($noHp, $message, $target, $messageFor);
            } else {
                $response =  $this->rejectProposal($data, $approve, null, $request->reason, '', 'Layanan Mahasiswa Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $response,
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function getData(Request $request)
    {
        $data = [];
        $admin = Gate::allows('admin');
        $dosen = Gate::allows('dosen');
        $kaprodi = Gate::allows('kaprodi');
        $minatBakat = Gate::allows('minat-bakat');
        $layananMahasiswa = Gate::allows('layanan-mahasiswa');
        $wakilRektor = Gate::allows('wakil-rektor');
        $data = Proposal::with(['user.userable.jurusan', 'dosen', 'ketua'])
            ->select('name', 'no_proposal', 'status', 'id', 'user_id', 'dosen_id', 'mahasiswa_id')
            ->where('status', '!=', 'Draft')
            ->when($dosen == true, function ($query) {
                $query->where('dosen_id', Auth::user()->userable_id);
            })
            ->when($kaprodi == true, function ($query) {
                $query->where(function ($query) {
                    $query->where('status', 'Pending Kaprodi')
                        ->orWhereRelation('ketua', 'jurusan_id', Auth::user()->userable->jurusan_id)
                        ->orWhereRelation('user.userable', 'jurusan_id', Auth::user()->userable->jurusan_id);
                })->where('dosen_id', Auth::user()->userable_id);
            })
            ->when($minatBakat == true, function ($query) {
                $query->where('is_acc_dosen', true);
            })
            ->when($layananMahasiswa == true, function ($query) {
                $query->where('is_acc_minat_bakat', true);
            })
            ->when($wakilRektor == true, function ($query) {
                $query->where('is_acc_layanan', true);
            })
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where(function ($item) use ($request) {
                    $item->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('no_proposal', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('user', 'name', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('user.userable.jurusan', 'name', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->getCollection()->transform(function ($item) use ($admin) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'ketua' => $item->ketua->name,
                'npm_ketua' => $item->ketua->npm,
                'no_proposal' => $item->no_proposal,
                'organisasi' => $item->user->userable->name,
                'jurusan' => $admin == true ? ($item->user->userable->jurusan  ?  $item->user->userable->jurusan->name : $item->ketua->jurusan->name) : '',
                'status' => $item->status,
                'admin' => $admin,
                'dosen' => $admin == true ? $item->dosen->name : '',
                'detail' => !in_array($item->status, ['draft', 'Draft']),
            ];
        });

        return response()->json([
            'status' => '200',
            'data' => $dataFormated,
            'currentPage' => $data->currentPage(),
            'totalPage' => $data->lastPage(),
        ], 200);
    }
}
