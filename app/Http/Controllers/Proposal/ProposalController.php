<?php

namespace App\Http\Controllers\Proposal;

use PDO;
use Dom\Attr;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Proposal;
use Illuminate\Http\Request;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ProposalHasMahasiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Helper\CrudController;

class ProposalController extends Controller
{
    public function index()
    {
        $head = ['No Proposal', 'Nama', 'Dosen'];
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
        $jurusan = !$admin ? Auth::user()->userable->jurusan_id : null;
        $organisasiOption = $this->getOrganisasiOption();
        $dosenOption = $this->getDosenOption($jurusan);
        $mahasiswaOption = $this->getMahasiswaOption();
        if (!$admin) {
            $organisasiOption = $organisasiOption->where('value', Auth::user()->id)->map(function ($item) {
                if ($item['value'] == Auth::id()) {
                    $item['selected'] = true;
                }
                return $item;
            });
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
            $filePath = '';
            $filePath = $this->storageStore($request->file('file'), 'proposal');

            $admin = Gate::allows('admin');
            if ($admin) {
                $unitKemahasiswaan = UnitKemahasiswaan::where('id', $request->user_id)->first();
            } else {
                $unitKemahasiswaan = Auth::user()->userable;
                if (!($unitKemahasiswaan instanceof UnitKemahasiswaan)) {
                    throw new \Exception('Organisasi yang dipilih bukan dari unit kemahasiswaan');
                }
            }
            $kodeJurusan = $unitKemahasiswaan->jurusan->kode;
            $romawi = $this->getRomawi(Carbon::now()->format('m'));
            $tahun = Carbon::now()->format('Y');

            DB::beginTransaction();

            $dosenEligible = Dosen::where('id', $request->dosen_id)
                ->lockForUpdate()
                ->first();

            if ($dosenEligible->status == false) {
                throw new \Exception('Dosen yang anda pilih sudah tidak aktif, silahkan refresh halamanan ini');
            }

            $lastRecord = Proposal::where('no_proposal', 'LIKE', '%/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun)
                ->orderBy('no_proposal', 'desc')
                ->first();

            if ($lastRecord == null) {
                $got = DB::selectOne("SELECT GET_LOCK('nomor_lock', 10)")->{"GET_LOCK('nomor_lock', 10)"};
                if ($got !== 1) {
                    throw new \Exception('Server sedang sibuk, Silahkan coba lagi!');
                }
            } else {
                $lastRecord = Proposal::where('no_proposal', 'LIKE', '%/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun)
                    ->orderBy('no_proposal', 'desc')
                    ->lockForUpdate()
                    ->first();
            }
            $lastRecord = Proposal::where('no_proposal', 'LIKE', '%/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun)
                ->orderBy('no_proposal', 'desc')
                ->lockForUpdate()
                ->first();

            $lastNumber = $lastRecord ? intval(explode('/', $lastRecord->no_proposal)[0]) : 0;
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            $noProposal = $newNumber  . '/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun;

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
                'no_proposal' => $noProposal,
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
            // Lepas lock
            if ($lastRecord == null) {
                DB::select("SELECT RELEASE_LOCK('bukti_lock')");
            }
            // insert mahasiswanya pakai table aja biar tidak mengulang
            $rows = collect($request->mahasiswa_id)->map(function ($id) use ($data) {
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
        $data = Proposal::findOrFail(decrypt($id));
        $edit = true;

        $admin = Gate::allows('admin');
        $jurusan = !$admin ? Auth::user()->userable->jurusan_id : null;
        $organisasiOption = $this->getOrganisasiOption();
        $dosenOption = $this->getDosenOption($jurusan);
        $mahasiswaOption = $this->getMahasiswaOption();
        $listMahasiswa = ProposalHasMahasiswa::where('proposal_id', $data->id)->pluck('mahasiswa_id')->toArray();

        if (!$admin) {
            $organisasiOption = $organisasiOption->where('value', Auth::user()->id)->map(function ($item) {
                if ($item['value'] == Auth::id()) {
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

        if (!in_array($data->status, ['Draft', 'Tolak'])) {
            return abort(404);
        }

        // set dosen agar ke select
        $dosenOption = $dosenOption->map(function ($item) use ($data) {
            if ($item['value'] == $data->dosen_id) {
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
        ];

        return view('Pages.Proposal.form', compact('organisasiOption', 'mahasiswaOption', 'dosenOption', 'data', 'edit'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'dosen_id' => 'required',
            'desc' => 'required',
            'start_date' => 'required_if:is_harian,false',
            'end_date' => 'required_if:is_harian,false',
            'mahasiswa_id' => 'required',
            'range_date' => 'required_if:is_harian,true',
            'file' => ['sometimes', File::types(['pdf'])->max(2 * 1024)],
        ], [
            'dosen_id.required' => 'Dosen penanggung jawab wajib dipilih',
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
            $data = Proposal::with(['mahasiswa'])->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            if (!$data) {
                throw new \Exception('Data proposal tidak ada atau telah dihapus, silahkan refresh halaman ini atau kembali ke halaman proposal');
            } elseif (!in_array($data->status, ['Draft', 'Tolak'])) {
                throw new \Exception('Tidak bisa merubah data proposal karena sudah diajukan');
            }

            if ($admin) {
                $unitKemahasiswaan = UnitKemahasiswaan::where('id', $request->user_id)->first();
            } else {
                $unitKemahasiswaan = Auth::user()->userable;
                if (!($unitKemahasiswaan instanceof UnitKemahasiswaan)) {
                    throw new \Exception('Organisasi yang dipilih bukan dari unit kemahasiswaan');
                }
            }

            $dosenEligible = Dosen::where('id', $request->dosen_id)
                ->lockForUpdate()
                ->first();

            if ($dosenEligible->status == false) {
                throw new \Exception('Dosen yang anda pilih sudah tidak aktif, silahkan refresh halamanan ini');
            }

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

            $Crud = new CrudController(Proposal::class, data: $data, id: $data->id, dataField: $dataField, description: 'Menambah Proposal', content: 'Proposal');
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

            $data = Proposal::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            if (!$data) {
                throw new \Exception('Data proposal tidak ada atau telah dihapus, silahkan refresh halaman ini atau kembali ke halaman proposal');
            } elseif (!in_array($data->status, ['Draft', 'Tolak'])) {
                throw new \Exception('Tidak bisa merubah data proposal karena sudah diajukan');
            }

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

    public function getData(Request $request)
    {
        $data = [];
        $admin = Gate::allows('admin');
        $data = Proposal::with(['user.userable.jurusan', 'dosen'])->select('name', 'no_proposal', 'status', 'id', 'user_id', 'dosen_id')
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
                'dosen' => $item->dosen->name,
                'edit' => in_array($item->status, ['draft', 'Draft']),
                'pengajuan' => in_array($item->status, ['draft', 'Draft', 'revisi']),
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
