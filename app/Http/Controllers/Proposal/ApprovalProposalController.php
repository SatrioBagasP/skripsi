<?php

namespace App\Http\Controllers\Proposal;

use Exception;
use Throwable;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Traits\UserValidation;
use App\Models\LaporanKegiatan;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApprovalProposalValidation;
use App\Http\Controllers\Helper\CrudController;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\ApprovalProposalRequestValidator;
use App\Http\Controllers\Notifikasi\NotifikasiController;

class ApprovalProposalController extends Controller
{
    use UserValidation, ApprovalProposalValidation;

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
        return view('Pages.Proposal.approval-index', compact('head'));
    }

    public function edit(Request $request, $id)
    {
        $data = Proposal::with('mahasiswa')
            ->where('id', decrypt($id))
            ->first();

        $this->validateExistingDataReturnAbort($data);
        $this->validateUserCanApprove($data, true);
        $this->validateProposalIsApproveable($data, true);

        $data = [
            'id' => encrypt($data->id),
            'ketua_id' => $data->mahasiswa_id,
            'name' => $data->name,
            'no_proposal' => $data->no_proposal,
            'desc' => $data->desc,
            'organisasi' => $data->pengusul->name,
            'dosen' => $data->dosen->name,
            'file_url' => Storage::temporaryUrl($data->file, now()->addMinutes(5)),
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'status' => $data->status,
            'mahasiswa' => $data->mahasiswa,
            'approvalBtn' => $this->getApprovalButton($data),
            'approvalUrl' => $this->getUrlApproval($data),
        ];

        $data['mahasiswa'] = $data['mahasiswa']->sortByDesc(function ($mhs) use ($data) {
            return $mhs->id == $data['ketua_id']; // ketua akan jadi true (1), lainnya false (0)
        });
        return view('Pages.Proposal.approval', compact('data'));
    }

    public function accProposal($proposal, $status, $desc, $reciever, $nextVerifikator, $messageFor)
    {
        try {
            $proposal->fill([
                'status' => $status,
            ]);
            $this->updateLog($proposal, $desc, 'Proposal');

            $notifikasi = new NotifikasiController();
            if ($messageFor == 'Pengajuan') {
                $message = $notifikasi->generateMessageForVerifikator(jenisPengajuan: 'Proposal', nama: $reciever->name ?? '-', judulKegiatan: $proposal->name, unitKemahasiswaan: $proposal->pengusul->name, route: route('approval-proposal.edit', encrypt($proposal->id)));
            } elseif ($messageFor == 'Diterima') {
                $message = $notifikasi->generateMessageForAccepted(jenisPengajuan: 'Proposal', judulKegiatan: $proposal->name, ketua: $reciever->name);
            }
            $message = $reciever ? $message : '-';
            $noHp = $reciever->no_hp ?? 0;

            $response = $notifikasi->sendMessage($noHp, $message, $nextVerifikator, $messageFor);
            return $response;
        } catch (Throwable $e) {
            throw new Exception($this->getErrorMessage($e));
        }
    }

    public function rejectProposal($proposal, $status, $reason, $desc)
    {
        try {
            $proposal->fill([
                'status' => $status,
                'alasan_tolak' => $reason,
            ]);
            $this->updateLog($proposal, $desc, 'Proposal');

            $notifikasi = new NotifikasiController();
            $noHp = $proposal->ketua->no_hp;

            $message = $notifikasi->generateMessageForRejected(jenisPengajuan: 'Proposal', alasanTolak: $reason, judulKegiatan: $proposal->name, unitKemahasiswaan: $proposal->pengusul->name, route: route('proposal.edit', encrypt($proposal->id)));
            $response = $notifikasi->sendMessage($noHp, $message, 'Ketua Pelaksana', 'Ditolak');

            return $response;
        } catch (Throwable $e) {
            throw new Exception($this->getErrorMessage($e));
        }
    }

    public function approvalDosen(Request $request)
    {
        $this->validateApprovalRequest($request);
        try {
            DB::beginTransaction();
            $data = Proposal::with([
                'mahasiswa',
                'pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data);
            $this->validateApprovalDosen($data);

            $nonJurusan = $data->pengusul->is_non_jurusan == true;
            $status = $nonJurusan ? 'Pending Layanan Mahasiswa' : 'Pending Kaprodi';

            if ($request->boolean('approve')) {
                // jika dia buka jurusan, maka langsung ambil layanan mahasiswanya siapa
                $jurusanId = $data->pengusul->jurusan_id;
                $reciever = $nonJurusan ? $this->getLayananMahasiswa() : $this->getKaprodi($jurusanId);
                $nextVerifikator = $nonJurusan ? 'Layanan Mahasiswa' : 'Kaprodi';
                $response = $this->accProposal($data, $status, 'Dosen Telah Menyetujui Proposal Anda!', $reciever, $nextVerifikator, 'Pengajuan');
            } else {
                $response = $this->rejectProposal($data, 'Rejected', $request->reason, 'Dosen Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
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
        $this->validateApprovalRequest($request);
        try {
            DB::beginTransaction();
            $data = Proposal::with([
                'mahasiswa',
                'pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data);
            $this->validateApprovalKaprodi($data);

            if ($request->boolean('approve')) {
                // $reciever = $this->getKepalaBagianMinatBakat();
                $reciever = $this->getMinatBakat();
                $response =  $this->accProposal($data, 'Pending Minat dan Bakat', 'Kaprodi Telah Menyetujui Proposal Anda!', $reciever, 'Akademik Minat dan Bakat', 'Pengajuan');
            } else {
                $response =  $this->rejectProposal($data, 'Rejected', $request->reason, 'Kaprodi Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
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
        $this->validateApprovalRequest($request);
        try {
            DB::beginTransaction();
            $data = Proposal::with([
                'mahasiswa',
                'pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data);
            $this->validateApprovalKetuaMinatBakat($data);

            if ($request->boolean('approve')) {
                $reciever = $this->getLayananMahasiswa();
                $response = $this->accProposal($data, 'Pending Layanan Mahasiswa', 'Kepala Bagian Minat dan Bakat Telah Menyetujui Proposal Anda!', $reciever, 'Layanan Mahasiswa', 'Pengajuan');
            } else {
                $response =  $this->rejectProposal($data, 'Rejected', $request->reason, 'Kepala Bagian Minat dan Bakat Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
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
        $this->validateApprovalRequest($request);
        try {
            DB::beginTransaction();
            $data = Proposal::with([
                'mahasiswa',
                'pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data);
            $this->validateApprovalLayananMahasiswa($data);

            if ($request->boolean('approve')) {
                $reciever = $this->getWakilRektor1();
                $response = $this->accProposal($data, 'Pending Wakil Rektor 1', 'Layanan Mahasiswa Menyetujui Proposal Anda!', $reciever, 'Wakil Rektor 1', 'Pengajuan');
            } else {
                $response =  $this->rejectProposal($data, 'Rejected', $request->reason, 'Layanan Mahasiswa Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
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
        $this->validateApprovalRequest($request);
        try {
            DB::beginTransaction();
            $data = Proposal::with([
                'ketua:id,name,no_hp',
                'pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data);
            $this->validateApprovalWakilRektor($data);

            if ($request->boolean('approve')) {
                $reciever = $data->ketua;
                $response =  $this->accProposal($data, 'Accepted', 'Wakil Rektor 1 Menyetujui Proposal Anda!', $reciever, 'Ketua Pelaksana', 'Diterima');
                $this->createLaporanKegiatan($data);
            } else {
                $response =  $this->rejectProposal($data, 'Rejected', $request->reason, 'Wakil Rektor 1 Telah Menolak Pengajuan Proposal Anda dengan alasan ' . $request->reason);
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

    public function createLaporanKegiatan($proposal)
    {
        try {
            LaporanKegiatan::updateOrCreate([
                'proposal_id' => $proposal->id,
            ], [
                'status' => 'Draft',
                'available_at' => $proposal->end_date,
            ]);
        } catch (Throwable $e) {
            throw new Exception($this->getErrorMessage($e));
        }
    }

    public function getData(Request $request)
    {
        $data = collect();
        $admin = Gate::allows('admin');
        $user = Auth::user()->load('userable.jurusan');

        $dosen = $this->validateUserIsDosen($user);
        $ketuaMinatDanBakat = $this->validateUserIsKetuaMinatBakat($user);
        $layananMahasiswa = $this->validateUserIsLayananMahasiswa($user);
        $wakilRektor = $this->validateUserIsWakilRektor1($user);
        $kaprodi = $this->validateUserIsKaprodi($user);

        if ($admin || $dosen) {
            $data = Proposal::with([
                'pengusul:id,jurusan_id,name',
                'pengusul.jurusan:name',
                'ketua:id,name,jurusan_id,npm',
                'ketua.jurusan:id,name',
                'dosen:id,name',
            ])
                ->when($admin == false, function ($q) use ($dosen, $user, $ketuaMinatDanBakat, $layananMahasiswa, $wakilRektor, $kaprodi) {
                    $q->when($dosen == true, function ($q) use ($user) {
                        $q->where('dosen_id', $user->userable_id)
                            ->whereNotIn('status', ['Draft', 'Rejected', 'Accepted']);
                    })
                        ->when($kaprodi == true, function ($q) use ($user) {
                            $q->orWhere(function ($q) use ($user) {
                                $q->where('status', 'Pending Kaprodi')
                                    ->whereRelation('pengusul', 'jurusan_id', $user->userable->jurusan_id); // krn jika dia itu tidak memiliki jurusan atau eksul, dia tidak perlu acc kaprodi
                            });
                        })
                        ->when($ketuaMinatDanBakat == true, function ($q) {
                            $q->orWhere('status', 'Pending Minat dan Bakat');
                        })
                        ->when($layananMahasiswa == true, function ($q) {
                            $q->orWhere('status', 'Pending Layanan Mahasiswa');
                        })
                        ->when($wakilRektor == true, function ($q) {
                            $q->orWhere('status', 'Pending Wakil Rektor 1');
                        });
                })
                ->when($request->search !== null, function ($query) use ($request) {
                    $query->where(function ($item) use ($request) {
                        $item->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('no_proposal', 'like', '%' . $request->search . '%')
                            ->orWhereRelation('pengusul', 'name', 'like', '%' . $request->search . '%')
                            ->orWhereRelation('pengusul.jurusan', 'name', 'like', '%' . $request->search . '%');
                    });
                })
                ->select('id', 'unit_id', 'mahasiswa_id', 'dosen_id', 'no_proposal', 'status', 'name')
                ->whereNotIn('status', ['Draft', 'Rejected', 'Accepted'])
                ->orderBy('id', 'desc')
                ->paginate($request->itemDisplay ?? 10);
        } else {
            $data = new LengthAwarePaginator([], 0, $request->itemDisplay ?? 10);
        }


        $dataFormated = $data->map(function ($item) use ($admin) {
            return [
                'id' => encrypt($item->id),
                'name' => $item->name,
                'ketua' => $item->ketua->name,
                'npm_ketua' => $item->ketua->npm,
                'no_proposal' => $item->no_proposal,
                'organisasi' => $item->pengusul->name,
                'jurusan' => $admin == true ? ($item->pengusul->jurusan  ?  $item->pengusul->jurusan->name : $item->ketua->jurusan->name) : '',
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

    public function getUrlApproval($proposal)
    {
        if ($proposal->status == 'Pending Dosen') {
            return route('approval-proposal.approvalDosen');
        } elseif ($proposal->status == 'Pending Kaprodi') {
            return route('approval-proposal.approvalKaprodi');
        } elseif ($proposal->status == 'Pending Minat dan Bakat') {
            return route('approval-proposal.approvalMinatBakat');
        } elseif ($proposal->status == 'Pending Layanan Mahasiswa') {
            return route('approval-proposal.approvalLayananMahasiswa');
        } elseif ($proposal->status == 'Pending Wakil Rektor 1') {
            return route('approval-proposal.approvalWakilRektor');
        } else {
            return null;
        }
    }

    public function getApprovalButton($proposal)
    {
        $admin = Gate::allows('admin');
        $user = Auth::user()->load('userable.jurusan');

        $dosen = $this->validateUserIsDosen($user);
        $ketuaMinatDanBakat = $this->validateUserIsKetuaMinatBakat($user);
        $layananMahasiswa = $this->validateUserIsLayananMahasiswa($user);
        $wakilRektor = $this->validateUserIsWakilRektor1($user);
        $kaprodi = $this->validateUserIsKaprodi($user);
        $dosenPj = $dosen ? $proposal->dosen_id == Auth::user()->userable_id : false;


        if (($proposal->status == 'Rejected' || $proposal->status == 'Draft' || $proposal->status == 'Accepted') && ($dosenPj || $admin)) {
            return false;
        } elseif ($proposal->status == 'Pending Dosen' && ($dosenPj || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Kaprodi' && ($kaprodi || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Minat dan Bakat' && ($ketuaMinatDanBakat || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Layanan Mahasiswa' && ($layananMahasiswa || $admin)) {
            return true;
        } elseif ($proposal->status == 'Pending Wakil Rektor 1' && ($wakilRektor || $admin)) {
            return true;
        } else {
            return false;
        }
    }
}
