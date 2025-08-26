<?php

namespace App\Http\Controllers\Proposal;

use PDO;
use Dom\Attr;
use Exception;
use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Dosen;
use App\Models\Proposal;
use Mockery\Matcher\Not;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Traits\DosenValidation;
use Illuminate\Validation\Rule;
use App\Traits\CommonValidation;
use App\Models\UnitKemahasiswaan;
use App\Traits\ProposalValidation;
use Illuminate\Support\Facades\DB;
use App\Traits\MahasiswaValidation;
use App\Http\Controllers\Controller;
use App\Models\ProposalHasMahasiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use App\Traits\ProposalRequestValidator;
use App\Traits\UnitKemahasiswaanValidation;
use App\Http\Controllers\Helper\CrudController;
use App\Http\Controllers\Notifikasi\NotifikasiController;

class ProposalController extends Controller
{
    // use ProposalRequestValidator;
    use MahasiswaValidation, DosenValidation, UnitKemahasiswaanValidation, ProposalValidation;

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

    public function getOption(Request $request)
    {
        try {
            $data = UnitKemahasiswaan::where('id', $request->id)
                ->first();
            $this->validateExistingData($data);
            $mahasiswaOption = $this->getMahasiswaOption($data->jurusan_id);
            $dosenOption = $this->getDosenOption($data->jurusan_id);
            return response()->json([
                'status' => 200,
                'data_mahasiswa' =>  $mahasiswaOption,
                'data_dosen' =>  $dosenOption,
            ], 200);
        } catch (\Throwable $e) {

            return response()->json([
                'status' => 400,
                'message' =>  $this->getErrorMessage($e),
            ], 400);
        }
    }

    public function create()
    {
        $organisasiOption = $this->getOrganisasiOption();

        if (Auth::user()->userable_type == UnitKemahasiswaan::class) {
            $organisasiOption = $organisasiOption->where('value', Auth::user()->userable_id)
                ->map(function ($item) {
                    if ($item['value'] == Auth::user()->userable_id) {
                        $item['selected'] = true;
                    }
                    return $item;
                });
        }
        return view('Pages.Proposal.form', compact('organisasiOption'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'unit_id' => 'required',
            'ketua_ids' => 'required',
            'dosen_id' => 'required',
            'desc' => 'required',
            'start_date' => ['date', Rule::requiredIf(fn() => !$request->boolean('is_harian'))],
            'end_date' => ['date', Rule::requiredIf(fn() => !$request->boolean('is_harian'))],
            'mahasiswa_id' => 'required',
            'range_date' => 'required_if:is_harian,true',
            'file' => ['required', File::types(['png'])->max(2 * 1024)],
        ], [
            'dosen_id.required' => 'Dosen penanggung jawab wajib dipilih',
            'unit_id.required' => 'Unit Kemahasiswaan  wajib dipilih',
            'ketua_ids.required' => 'Ketua Pelaksana Wajib Diisi',
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
        $filePath = '';
        try {
            DB::beginTransaction();

            $unitKemahasiswaan = $this->validateUnitKemahasiswaanIsActive($request->unit_id);
            $kodeJurusan = $unitKemahasiswaan->is_non_jurusan == false ? $unitKemahasiswaan->jurusan->kode : '-';
            $romawi = $this->getRomawi(Carbon::now()->format('m'));
            $tahun = Carbon::now()->format('Y');
            $nomorProposal = $this->validateNomorProposal($kodeJurusan, $romawi, $tahun);

            $this->validateMahasiswaIsActive($request->ketua_ids);
            $this->validateDosenIsActive($request->dosen_id);
            [$startDate, $endDate] = $this->validateDate($request);
            $filePath = $this->storageStore($request->file('file'), 'proposal');

            $data = Proposal::create([
                'name' => $request->name,
                'mahasiswa_id' => $request->ketua_ids,
                'no_proposal' => $nomorProposal,
                'desc' => $request->desc,
                'dosen_id' => $request->dosen_id,
                'unit_id' => $request->unit_id,
                'file' => $filePath,
                'is_harian' => $request->boolean('is_harian'),
                'status' => 'Draft',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            $mahasiswaId = $request->mahasiswa_id ?? [];
            $ketuaId = $request->ketua_ids;
            if (!in_array($ketuaId, $mahasiswaId)) {
                array_unshift($mahasiswaId, $ketuaId);
            }

            $data->mahasiswa()->attach($mahasiswaId);
            $this->storeLog($data, 'Menambah Proposal', 'Proposal');

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getStoreSuccessMessage(),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->storageDelete($filePath);
            return response()->json([
                'status' => 400,
                'message' => $this->getErrorMessage($e),
            ], 400);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $data = Proposal::where('id', decrypt($id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingData($data);
            $this->validateProposalIsEditable($data);
            $organisasiOption = $this->getOrganisasiOption();
            $edit = true;

            if (Auth::user()->userable_type == UnitKemahasiswaan::class) {
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

            $range = null;
            if ($data->is_harian) {
                $range = Carbon::parse($data->start_date)->format('Y-m-d') . ' to ' . Carbon::parse($data->end_date)->format('Y-m-d');
            }

            $data = [
                'id' => encrypt($data->id),
                'name' => $data->name,
                'desc' => $data->desc,
                'file_url' => Storage::temporaryUrl($data->file, now()->addMinutes(5)),
                'is_harian' => $data->is_harian,
                'start_date' => Carbon::parse($data->start_date)->format('Y-m-d H:i'),
                'end_date' => Carbon::parse($data->end_date)->format('Y-m-d H:i'),
                'range_date' => $range,
                'unit_id' => $data->unit_id,
                'dosen_id' => $data->dosen_id,
                'ketua_ids' => $data->mahasiswa_id,
                'selected_mahasiswa' => $data->mahasiswa->filter(function ($q) use ($data) {
                    return $q->id != $data->mahasiswa_id;
                })
                    ->pluck('id')
                    ->toArray(),
            ];
            return view('Pages.Proposal.form', compact('organisasiOption', 'data', 'edit'));
        } catch (Throwable $e) {
            return abort(500, $this->getErrorMessage($e));
        }

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
            'ketua_ids' => 'required',
            'dosen_id' => 'required',
            'desc' => 'required',
            'start_date' => 'required_if:is_harian,false',
            'end_date' => 'required_if:is_harian,false',
            'mahasiswa_id' => 'required',
            'range_date' => 'required_if:is_harian,true',
            'file' => ['sometimes', File::types(['pdf'])->max(2 * 1024)],
        ], [
            'dosen_id.required' => 'Dosen penanggung jawab wajib dipilih',
            'ketua_ids.required' => 'Ketua Pelaksana Wajib Diisi',
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
            $data = Proposal::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateProposalIsEditable($data);
            $this->validateProposalIsEditable($data);
            $this->validateUnitKemahasiswaanIsActive($request->unit_id);
            $this->validateMahasiswaIsActive($request->ketua_ids);
            $this->validateDosenIsActive($request->dosen_id);
            [$startDate, $endDate] = $this->validateDate($request);

            $filePath = '';
            $oldPath = $data->file;
            if ($request->file('file')) {
                $filePath = $this->storageStore($request->file('file'), 'proposal');
            }

            $data->fill([
                'name' => $request->name,
                'mahasiswa_id' => $request->ketua_ids,
                'desc' => $request->desc,
                'dosen_id' => $request->dosen_id,
                'unit_id' => $request->unit_id,
                'file' => $request->file('file') ? $filePath : $oldPath,
                'is_harian' => $request->boolean('is_harian'),
                'status' => 'Draft',
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            $mahasiswaId = $request->mahasiswa_id ?? [];
            $ketuaId = $request->ketua_ids;
            if (!in_array($ketuaId, $mahasiswaId)) {
                array_unshift($mahasiswaId, $ketuaId);
            }
            $data->mahasiswa()->sync($mahasiswaId);
            $this->updateLog($data, 'Merubah Proposal', 'Proposal');

            if ($request->file('file')) {
                $this->storageDelete($oldPath);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getUpdateSuccessMessage(),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->storageDelete($filePath);

            return response()->json([
                'status' => 400,
                'message' => $this->getErrorMessage($e),
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
            $this->validateExistingData($data);
            $this->validateProposalIsEditable($data);
            $this->validateProposalOwnership($data);
            $this->deleteLog($data, 'Menghapus Propsal', 'Proposal');
            $data->delete();

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getDeleteSuccessMessage(),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => $this->getErrorMessage($e),
            ], 400);
        }
    }

    public function pengajuan(Request $request)
    {
        try {
            DB::beginTransaction();
            $admin = Gate::allows('admin');
            $data = Proposal::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();
            $this->validateExistingData($data);
            $this->validateProposalIsEditable($data);
            $dosen = $this->validateDosenIsActive($data->dosen_id);
            $this->validateProposalOwnership($data);
            $noHp = $dosen ? $dosen->no_hp : 0;

            $notifikasi = new NotifikasiController();
            $message = $dosen ? $notifikasi->generateMessageForVerifikator(jenisPengajuan: 'Proposal', nama: $dosen->name, judulKegiatan: $data->name, unitKemahasiswaan: $data->pengusul->name, route: route('approval-proposal.edit', encrypt($data->id))) : '-';

            $response = $notifikasi->sendMessage($noHp, $message, 'Dosen Penanggung Jawab', 'Pengajuan');

            $data->fill([
                'status' => 'Pending Dosen',
            ]);

            $desc =  $admin ?  'Admin telah mengajukan proposal anda' : 'Berhasil mengajukan proposal';

            $this->updateLog($data, $desc, 'Proposal');
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

    public function getData(Request $request)
    {
        $data = [];
        $admin = Gate::allows('admin');
        $data = Proposal::with(['pengusul.jurusan', 'dosen', 'ketua'])->select('name', 'no_proposal', 'status', 'id', 'unit_id', 'dosen_id', 'mahasiswa_id')
            ->when(Auth::user()->userable_type == UnitKemahasiswaan::class, function ($query) {
                $query->where('unit_id', Auth::user()->userable_id);
            })
            ->when($request->search !== null, function ($query) use ($request) {
                $query->where(function ($item) use ($request) {
                    $item->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('no_proposal', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('pengusul', 'name', 'like', '%' . $request->search . '%')
                        ->orWhereRelation('pengusul.jurusan', 'name', 'like', '%' . $request->search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($request->itemDisplay ?? 10);

        $dataFormated = $data->map(function ($item) use ($admin) {
            return [
                'id' => encrypt($item->id),
                'status' => $item->status,
                'name' => $item->name,
                'ketua' => $item->ketua->name,
                'npm_ketua' => $item->ketua->npm,
                'no_proposal' => $item->no_proposal,
                'organisasi' => $admin == true ? $item->pengusul->name : '',
                'jurusan' => $admin == true ? ($item->pengusul->jurusan  ?  $item->pengusul->jurusan->name : $item->ketua->jurusan->name) : '',
                'admin' => $admin,
                'dosen' => $item->dosen->name,
                'edit' => in_array($item->status, ['Draft', 'Rejected']),
                'pengajuan' => in_array($item->status, ['Draft', 'Rejected']),
                'delete' => in_array($item->status, ['Draft', 'Rejected']),
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
