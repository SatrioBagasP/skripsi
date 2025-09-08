<?php

namespace App\Http\Controllers\LaporanKegiatan;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Models\LaporanKegiatan;
use App\Models\UnitKemahasiswaan;
use App\Traits\JurusanValidation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\UserValidation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LaporanKegiatanController extends Controller
{
    use UserValidation;

    public function index()
    {
        $head = ['No Proposal', 'Nama'];
        $admin = Gate::allows('admin');
        if ($admin) {
            $head[] = 'Organisasi';
            $head[] = 'Jurusan';
        }

        $head[] = 'Status';
        $head[] = 'Aksi';
        return view('Pages.LaporanKegiatan.index', compact('head'));
    }

    public function edit(Request $request, $id) {}

    public function update(Request $request) {}

    public function getData(Request $request)
    {
        $data = [];
        $admin = Gate::allows('admin');
        $unitKemahasiswaan = $this->validateUserIsUnitKemahasiswaan(Auth::user());
        $now = Carbon::now();
        $data = LaporanKegiatan::with([
            'proposal:id,name,no_proposal,unit_id,mahasiswa_id',
            'proposal.pengusul:id,name,jurusan_id',
            'proposal.pengusul.jurusan:id,name',
            'proposal.ketua:id,jurusan_id',
            'proposal.ketua.jurusan:id,name',
        ])
            ->select('proposal_id', 'status', 'id', 'available_at')
            ->when($unitKemahasiswaan == true, function ($query) {
                $query->whereRelation('proposal', 'unit_id', Auth::user()->userable_id);
            })
            ->where('available_at', '<=', $now)
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where(function ($item) use ($request) {
                    $item->whereRelation('proposal', 'name', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('proposal', 'no_proposal', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('proposal.pengusul', 'name', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('proposal.pengusul.jurusan', 'name', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->map(function ($item) use ($admin) {
            return [
                'id' => encrypt($item->id),
                'status' => $item->status,
                'name' => $item->proposal->name,
                'no_proposal' => $item->proposal->no_proposal,
                'organisasi' => $admin == true ? $item->proposal->pengusul->name : '',
                'jurusan' => $admin == true ? ($item->proposal->pengusul->jurusan  ?  $item->proposal->pengusul->jurusan->name : $item->proposal->ketua->jurusan->name) : '',
                'admin' => $admin,
                'edit' => in_array($item->status, ['Draft', 'Rejected']),
                'pengajuan' => in_array($item->status, ['Draft', 'Rejected']),
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
