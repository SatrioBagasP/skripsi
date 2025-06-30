<?php

namespace App\Http\Controllers\Proposal;

use Carbon\Carbon;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Helper\CrudController;

class ProposalController extends Controller
{
    public function index()
    {
        $head = ['No Proposal', 'Nama'];
        if (Auth::user()->role_id == 1) {
            $head[] = 'Organisasi';
            $head[] = 'Jurusan';
        }

        $head[] = 'Status';
        $head[] = 'Aksi';
        return view('Pages.Proposal.index', compact('head'));
    }

    public function create()
    {
        $admin = Gate::allows('admin');
        $organisasiOption = $this->getOrganisasiOption();
        $dosenOption = $this->getDosenOption();
        $mahasiswaOption = [];
        if (!$admin) {
            $organisasiOption = $organisasiOption->where('value', Auth::user()->id)->map(function ($item) {
                if ($item['value'] == Auth::id()) {
                    $item['selected'] = true;
                }
                return $item;
            });
            $mahasiswaOption = $this->getMahasiswaOption();
        }

        return view('Pages.Proposal.form', compact('organisasiOption', 'mahasiswaOption', 'dosenOption'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'dosen_id' => 'required',
            'desc' => 'required',
            'start_date' => 'required_if:is_harian,false',
            'end_date' => 'required_if:is_harian,false',
            'mahasiswa_id' => 'required',
            'range_date' => 'required_if:is_harian,true',
            'file' => ['required', File::types(['pdf'])->max(2 * 1024)],
        ], [
            'dosen_id.required' => 'Dosen penanggung jawab wajib dipilih',
            'name.required' => 'Judul proposal wajib diisi',
            'desc.required' => 'Deskripsi wajib diisi',
            'start_date.required_if' => 'Jadwal mulai wajib diisi',
            'end_date.required_if' => 'Jadwal berakhir wajib diisi',
            'range_date.required_if' => 'Jadwal wajib diisi',
            'mahasiswa_id.required' => 'Mahasiswa wajib dipilih',
            'file.required' => 'File proposal wajib diupload',
            'file.max' => 'Ukuran file maksimal 2MB.',
            'file.mimes' => 'File harus berupa PDF.',
        ]);

        try {
            $filePath = null;
            $filePath = $this->storageStore($request->file('file'), 'proposal');
            return DB::transaction(function () use ($request, $filePath) {
                $admin = Gate::allows('admin');
                if ($request->boolean('is_harian')) {
                    $range = strpos($request->range_date, 'to');
                    if ($range == false) {
                        throw new \Exception('Start - End Date tidak boleh satu hari saja!, jika harian, silahkan click checkbox harian');
                    }
                    list($startDate, $endDate) = explode(' to ', $request->range_date);
                    $startDate = Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', $endDate)->startOfDay();
                } else {
                    $startDate = Carbon::createFromFormat('Y-m-d H:i', $request->start_date);
                    $endDate = Carbon::createFromFormat('Y-m-d H:i', $request->end_date);

                    if ($endDate->isBefore($startDate)) {
                        throw new \Exception('Ups...! End date anda lebih duluan dari pada start date');
                    }
                }

                $dataField = [
                    'name' => $request->name,
                    'desc' => $request->desc,
                    'no_proposal' => '321',
                    'dosen_id' => $request->dosen_id,
                    'user_id' => $admin == true ? $request->user_id : Auth::user()->id,
                    'file' => $filePath,
                    'is_harian' => $request->boolean('is_harian'),
                    'status' => 'Draft',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ];

                $Crud = new CrudController(Proposal::class, dataField: $dataField, description: 'Menambah Proposal', content: 'Proposal');
                $data = $Crud->insertWithReturnData();

                // insert mahasiswanya pakai table aja agar tidak mengulang
                $rows = collect($request->mahasiswa_id)->map(function ($id) use ($data) {
                    return [
                        'proposal_id'  => $data->id,
                        'mahasiswa_id' => $id,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                })->toArray();
                DB::table('proposal_has_mahasiswa')->insert($rows);

                return response()->json([
                    'status' => 200,
                    'message' => 'Data Berhasil Ditambahkan',
                ], 200);
            });
        } catch (\Throwable $e) {
            Storage::delete($filePath);
            if (app()->environment('local')) {
                $message = $e->getMessage() . ' Line: ' . $e->getLine() . ' on ' . $e->getFile();
            } else {
                $message = $e->getMessage();
            }
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
        $data = Proposal::with(['user.userable.jurusan'])->select('name', 'no_proposal', 'status', 'id', 'user_id')
            ->when($admin == false, function ($query) {
                $query->where('user_id', Auth::user()->id);
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
                'jurusan' => $item->user->userable->jurusan->name,
                'status' => $item->status,
                'admin' => $admin,
                'edit' => in_array($item->status, ['draft', 'Draft']),
                'delete' => in_array($item->status, ['Draft', 'draft', 'revisi']),
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
