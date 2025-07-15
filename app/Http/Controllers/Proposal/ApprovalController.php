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
        $head = ['No Proposal', 'Nama', 'Organisasi'];
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
        $data = [
            'id' => encrypt($data->id),
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
            'approvalBtn' => $this->approvalEligible($data),
            'approvalUrl' => $this->urlProposalEligible($data),
        ];
        return view('Pages.Proposal.validasi', compact('data'));
    }

    public function accProposal(Request $request, $approve, $field, $status, $desc)
    {

        $data = $this->validateApprovalProposalEligible($request);
        $valid = $this->approvalEligible($data);
        if(!$valid){
            throw new \Exception('Data tidak valid untuk disetujui atau ditolak, silahkan refersh halaman ini!');
        }
        $dataField = [
            $field => $approve,
            'status' => 'Pending Kaprodi',
        ];
        $Crud = new CrudController(Proposal::class, data: $data, id: $data->id, dataField: $dataField, description: 'Dosen telah menyetujui proposal anda', content: 'Approval Proposal');
        $data = $Crud->updateWithReturnData();

        return $data;
    }

    public function rejectProposal(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $this->validateApprovalProposalEligible($request);
            $dataField = [
                $request->field => false,
                'status' => 'Tolak',
                'alasan_tolak' => $request->reason,
            ];
            $Crud = new CrudController(Proposal::class, data: $data, id: $data->id, dataField: $dataField, description: 'Dosen telah menolak pengajuan proposal anda, silahkan revisi dan ajukan kembali!', content: 'Approval Proposal');
            $data = $Crud->updateWithReturnData();

            $user = $data->user->userable;

            $notifikasi = new NotifikasiController();
            $response = $notifikasi->sendMessage($user, 'Ditolak Wlee');

            $notifGagal = false;
            $alasanNotif = '';
            if ($response['status'] == false) {
                $notifGagal = true;
                $alasanNotif = $response['reason'] ?? 'Tidak diketahui';
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Pengajuan Berhasil Ditolak' . ($notifGagal ? '. Namun notifikasi tidak berhasil dikirim dikarenakan' . $alasanNotif . '. Silakan hubungi organisasi secara langsung atau minta admin memperbarui nomor organisasi.' : ''),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
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

            $data =  $this->accProposal($request, $approve, 'is_acc_dosen', 'Pending Kaprodi', 'Dosen Telah Menyetujui Proposal Anda!');

            $noProposal = explode('/', $data->no_proposal);
            $kodeJurusan = $noProposal[1];

            $jurusan = Jurusan::select(['id'])->where('kode', $kodeJurusan)->first();
            $kaprodi = $this->getKaprodi($jurusan->id);
            $noHp = $kaprodi ? $kaprodi->no_hp : '0';

            $notifikasi = new NotifikasiController();
            $response = $notifikasi->sendMessage($noHp, 'Acc kaprodi');

            dd($response);

            DB::commit();
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
        $data = Proposal::with(['user.userable.jurusan', 'dosen'])->select('name', 'no_proposal', 'status', 'id', 'user_id', 'dosen_id')
            ->when($admin == false, function ($query) {
                $query->where('dosen_id', Auth::user()->userable_id)
                    ->where('status', '!=', 'Draft');
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
                'no_proposal' => $item->no_proposal,
                'organisasi' => $item->user->userable->name,
                'jurusan' => $admin == true ? $item->user->userable->jurusan->name : '',
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
