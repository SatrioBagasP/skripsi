<?php

namespace App\Http\Controllers\Proposal;

use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApprovalProposalRequestValidator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
        $data = $this->validateApprovalProposalStatus($request);
        $data = [
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
        ];
        return view('Pages.Proposal.validasi', compact('data'));
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

    public function accDosen(Request $request)
    {
        $request->dd();
    }
}
