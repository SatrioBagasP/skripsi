<?php

namespace App\Http\Controllers\Proposal;

use PDO;
use Dom\Attr;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Proposal;
use Mockery\Matcher\Not;
use Illuminate\Http\Request;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProposalHasMahasiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use App\Traits\ProposalRequestValidator;
use App\Http\Controllers\Helper\CrudController;
use App\Http\Controllers\Notifikasi\NotifikasiController;
use App\Models\Mahasiswa;

class ProposalController extends Controller
{
    use ProposalRequestValidator;

    public function index()
    {
        $head = ['No Proposal', 'Nama', 'Ketua Pelaksana', 'Dosen'];
        $admin = Gate::allows('admin');
        if ($admin) {
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
        $jurusan = !$admin ? Auth::user()->userable->jurusan_id : null;
        $organisasiOption = $this->getOrganisasiOption();
        $dosenOption = $this->getDosenOption($jurusan);
        $ketuaOption = $this->getMahasiswaOption($jurusan);
        $mahasiswaOption = $this->getMahasiswaOption($jurusan);

        if (!$admin) {
            $organisasiOption = $organisasiOption->where('value', Auth::user()->userable_id)->map(function ($item) {
                if ($item['value'] == Auth::user()->userable_id) {
                    $item['selected'] = true;
                }
                return $item;
            });
        }

        return view('Pages.Proposal.form', compact('organisasiOption', 'mahasiswaOption', 'ketuaOption', 'dosenOption'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'ketua_id' => 'required',
            'dosen_id' => 'required',
            'desc' => 'required',
            'start_date' => 'required_if:is_harian,false',
            'end_date' => 'required_if:is_harian,false',
            'mahasiswa_id' => 'required',
            'range_date' => 'required_if:is_harian,true',
            'file' => ['required', File::types(['pdf'])->max(2 * 1024)],
        ], [
            'dosen_id.required' => 'Dosen penanggung jawab wajib dipilih',
            'ketua_id.required' => 'Ketua Pelaksana Wajib Diisi',
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
        $proposal = null;
        $filePath = '';
        try {
            $admin = Gate::allows('admin');
            $unitKemahasiswaanEligible = $this->validateUnitKemahasiswaan($request, $admin);
            $kodeJurusan = $this->validateJurusan($unitKemahasiswaanEligible, $request);
            $romawi = $this->getRomawi(Carbon::now()->format('m'));
            $tahun = Carbon::now()->format('Y');

            // buat temp proposal
            $proposal = $this->validateProposal($kodeJurusan, $romawi, $tahun);

            DB::beginTransaction();
            $dosenEligible = $this->validateDosen($request->dosen_id);
            $ketuaEligible = $this->validateKetua($request->ketua_id);
            [$startDate, $endDate] = $this->validateDate($request);

            $filePath = $this->storageStore($request->file('file'), 'proposal');

            $dataField = [
                'name' => $request->name,
                'mahasiswa_id' => $request->ketua_id,
                'desc' => $request->desc,
                'dosen_id' => $request->dosen_id,
                'user_id' => $admin == true ? $request->user_id : Auth::user()->id,
                'file' => $filePath,
                'is_harian' => $request->boolean('is_harian'),
                'status' => 'Draft',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];

            $Crud = new CrudController(Proposal::class, data: $proposal, dataField: $dataField, description: 'Menambah Proposal', content: 'Proposal');
            $data = $Crud->updateWithReturnData();

            $mahasiswaId = $request->mahasiswa_id ?? [];
            $ketuaId = $request->ketua_id;

            if (!in_array($ketuaId, $mahasiswaId)) {
                array_unshift($mahasiswaId, $ketuaId);
            }

            // insert mahasiswanya pakai table aja biar tidak mengulang
            $rows = collect($mahasiswaId)->map(function ($id) use ($data) {
                return [
                    'proposal_id'  => $data->id,
                    'mahasiswa_id' => $id,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            })->toArray();
            DB::table('proposal_has_mahasiswa')->insert($rows);

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Data Berhasil Ditambahkan',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($proposal) {
                $proposal->delete();
            }
            $this->storageDelete($filePath);
            $message = $this->getErrorMessage($e);
            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function edit(Request $request, $id)
    {
        $data = $this->validateProposalEligible($request);
        $edit = true;

        $admin = Gate::allows('admin');
        $jurusan = !$admin ? Auth::user()->userable->jurusan_id : null;
        $organisasiOption = $this->getOrganisasiOption();
        $dosenOption = $this->getDosenOption($jurusan);
        $mahasiswaOption = $this->getMahasiswaOption($jurusan);
        $ketuaOption = $this->getMahasiswaOption($jurusan);
        $listMahasiswa = ProposalHasMahasiswa::where('proposal_id', $data->id)->pluck('mahasiswa_id')->toArray();
        $hasJurusan = $jurusan == null ? false : true;

        if (!$admin) {
            $organisasiOption = $organisasiOption->where('value', Auth::user()->userable_id)->map(function ($item) {
                if ($item['value'] == Auth::user()->userable_id) {
                    $item['selected'] = true;
                }
                return $item;
            });
        } else {
            $organisasiOption = $organisasiOption->map(function ($item) use ($data) {
                if ($item['value'] == $data->user_id) {
                    $item['selected'] = true;
                }
                return $item;
            });
        }
        if ($hasJurusan) {
            // set dosen agar ke select
            $dosenOption = $dosenOption->map(function ($item) use ($data) {
                if ($item['value'] == $data->dosen_id) {
                    $item['selected'] = true;
                }
                return $item;
            });
        }

        $ketuaOption = $ketuaOption->map(function ($item) use ($data) {
            if ($item['value'] == $data->mahasiswa_id) {
                $item['selected'] = true;
            }
            return $item;
        });

        // set mahasiswanya yang ke select
        $mahasiswaOption = $mahasiswaOption->map(function ($item) use ($listMahasiswa) {
            if (in_array($item['value'], $listMahasiswa)) {
                $item['selected'] = true;
            }
            return $item;
        });

        $range = null;
        if ($data->is_harian) {
            $range = Carbon::parse($data->start_date)->format('Y-m-d') . ' to ' . Carbon::parse($data->end_date)->format('Y-m-d');
        }

        // ini maping data ulang untuk dikirim ke bladenya
        $data = [
            'id' => encrypt($data->id),
            'name' => $data->name,
            'desc' => $data->desc,
            'file_url' => Storage::temporaryUrl($data->file, now()->addMinutes(5)),
            'is_harian' => $data->is_harian,
            'start_date' => Carbon::parse($data->start_date)->format('Y-m-d H:i'),
            'end_date' => Carbon::parse($data->end_date)->format('Y-m-d H:i'),
            'range_date' => $range,
            'dosen_id' => $data->dosen_id,
        ];

        return view('Pages.Proposal.form', compact('organisasiOption', 'mahasiswaOption', 'ketuaOption', 'dosenOption', 'data', 'edit', 'hasJurusan'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'ketua_id' => 'required',
            'dosen_id' => 'required',
            'desc' => 'required',
            'start_date' => 'required_if:is_harian,false',
            'end_date' => 'required_if:is_harian,false',
            'mahasiswa_id' => 'required',
            'range_date' => 'required_if:is_harian,true',
            'file' => ['sometimes', File::types(['pdf'])->max(2 * 1024)],
        ], [
            'dosen_id.required' => 'Dosen penanggung jawab wajib dipilih',
            'ketua_id.required' => 'Judul proposal wajib diisi',
            'name.required' => 'Judul proposal wajib diisi',
            'desc.required' => 'Deskripsi wajib diisi',
            'start_date.required_if' => 'Jadwal mulai wajib diisi',
            'end_date.required_if' => 'Jadwal berakhir wajib diisi',
            'range_date.required_if' => 'Jadwal wajib diisi',
            'mahasiswa_id.required' => 'Mahasiswa wajib dipilih',
            'file.max' => 'Ukuran file maksimal 2MB.',
            'file.mimes' => 'File harus berupa PDF.',
        ]);
        try {
            DB::beginTransaction();

            $admin = Gate::allows('admin');
            $filePath = '';
            $data = $this->validateProposalEligible($request);

            $unitKemahasiswaanEligible = $this->validateUnitKemahasiswaan($request, $admin);
            $dosenEligible = $this->validateDosen($request->dosen_id);
            $ketuaEligible = $this->validateKetua($request->ketua_id);
            [$startDate, $endDate] = $this->validateDate($request);

            $dataField = [
                'name' => $request->name,
                'ketua_id' => $request->ketua_id,
                'desc' => $request->desc,
                'dosen_id' => $request->dosen_id,
                'user_id' => $admin == true ? $request->user_id : Auth::user()->id,
                'is_harian' => $request->boolean('is_harian'),
                'status' => 'Draft',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];

            $oldPath = $data->file;
            if ($request->file('file')) {
                $filePath = $this->storageStore($request->file('file'), 'proposal');
                $dataField['file'] = $filePath;
            }

            $mahasiswaDefault = $data->mahasiswa->pluck('id');
            $mahasiswaInsert = array_diff($request->mahasiswa_id, $mahasiswaDefault->toArray()); // ini yang insert
            $mahasiswaDeleted = array_diff($mahasiswaDefault->toArray(), $request->mahasiswa_id); // ini yang didelete

            ProposalHasMahasiswa::where('proposal_id', $data->id)
                ->whereIn('mahasiswa_id', $mahasiswaDeleted)
                ->delete();

            $ketuaId = $request->ketua_id;

            if (!in_array($ketuaId, $mahasiswaInsert)) {
                array_unshift($mahasiswaInsert, $ketuaId);
            }

            // insert mahasiswanya pakai table aja agar tidak mengulang
            $rows = collect($mahasiswaInsert)->map(function ($id) use ($data) {
                return [
                    'proposal_id'  => $data->id,
                    'mahasiswa_id' => $id,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            })->toArray();
            DB::table('proposal_has_mahasiswa')->insert($rows);

            $Crud = new CrudController(Proposal::class, data: $data, id: $data->id, dataField: $dataField, description: 'Merubah Proposal', content: 'Proposal');
            $data = $Crud->updateWithReturnData();

            if ($request->file('file')) {
                $this->storageDelete($oldPath);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Data Berhasil Ditambahkan',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->storageDelete($filePath);
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $this->validateProposalEligible($request);

            $Crud = new CrudController(Proposal::class, data: $data, id: $data->id, description: 'Menghapus Proposal', content: 'Proposal');
            $action = $Crud->deleteWithReturnJson();

            DB::commit();
            return $action;
        } catch (\Throwable $e) {
            DB::rollBack();
            $message = $this->getErrorMessage($e);

            return response()->json([
                'status' => 400,
                'message' => $message,
            ], 400);
        }
    }

    public function pengajuan(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $this->validateProposalEligible($request);
            $dosen = $this->validateDosen($data->dosen_id);
            $noHp = $dosen ? $dosen->no_hp : 0;
            $notifikasi = new NotifikasiController();
            $response = $notifikasi->sendMessage($noHp, 'Tolong ACC', 'Dosen Penanggung Jawab','Pengajuan');

            $data->status = 'Pending Dosen';
            $data->save();

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

    public function getDosen(Request $request)
    {
        try {
            $mahasiswa = Mahasiswa::select('jurusan_id')
                ->where('id', $request->ketuaId)
                ->first();

            if (!$mahasiswa) {
                throw new \Exception('Data mahasiswa tidak ditemukan, silahkan refresh halaman ini');
            }

            $data = $this->getDosenOption($mahasiswa->jurusan_id);
            return response()->json([
                'status' => 200,
                'data' => $data,
            ], 200);
        } catch (\Throwable $e) {
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
        $data = Proposal::with(['user.userable.jurusan', 'dosen', 'ketua'])->select('name', 'no_proposal', 'status', 'id', 'user_id', 'dosen_id', 'mahasiswa_id')
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
                'status' => $item->status,
                'name' => $item->name,
                'ketua' => $item->ketua->name,
                'npm_ketua' => $item->ketua->npm,
                'no_proposal' => $item->no_proposal,
                'organisasi' => $admin == true ? $item->user->userable->name : '',
                'jurusan' => $admin == true ? ($item->user->userable->jurusan  ?  $item->user->userable->jurusan->name : $item->ketua->jurusan->name) : '',
                'admin' => $admin,
                'dosen' => $item->dosen->name,
                'edit' => in_array($item->status, ['draft', 'Draft', 'tolak', 'Rejected']),
                'pengajuan' => in_array($item->status, ['draft', 'Draft', 'tolak', 'Rejected']),
                'delete' => in_array($item->status, ['Draft', 'draft', 'tolak', 'Rejected']),
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
