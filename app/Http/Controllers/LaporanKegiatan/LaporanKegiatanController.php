<?php

namespace App\Http\Controllers\LaporanKegiatan;

use Throwable;
use Carbon\Carbon;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\BuktiDukung;
use Illuminate\Http\Request;
use App\Traits\UserValidation;
use App\Models\LaporanKegiatan;
use Illuminate\Validation\Rule;
use App\Models\UnitKemahasiswaan;
use App\Traits\JurusanValidation;
use App\Traits\ProposalValidation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Notifikasi\NotifikasiController;
use App\Traits\LaporanKegiatanValidation;

class LaporanKegiatanController extends Controller
{
    use UserValidation, ProposalValidation, LaporanKegiatanValidation;

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
        return view('Pages.LaporanKegiatan.index', compact('head'));
    }

    public function edit(Request $request, $id)
    {

        $data = LaporanKegiatan::with([
            'proposal:id,name,no_proposal,unit_id,file',
            'buktiDukung:id,laporan_kegiatan_id,file',
        ])
            ->where('id', decrypt($id))
            ->first();

        $this->validateExistingDataReturnAbort($data);
        $this->validateProposalOwnership($data->proposal, 'laporan kegiatan','Abort');
        $this->validateLaporanKegiatanIsEditable($data, 'Abort');
        $this->validateProposalIsEditable($data, 'laporan kegiatan', true);

        $data = [
            'id' => encrypt($data->id),
            'name' => $data->proposal->no_proposal . ' - ' . $data->proposal->name,
            'file_proposal' => Storage::temporaryUrl($data->proposal->file, now()->addMinutes(5)),
            'file' => $data->file ? Storage::temporaryUrl($data->file, now()->addMinutes(5)) : null,
            'alasan_tolak' => $data->alasan_tolak,
            'file_bukti_kehadiran' => $data->file_bukti_kehadiran ? Storage::temporaryUrl($data->file_bukti_kehadiran, now()->addMinutes(5)) : null,
            'bukti_dukung' => $data->buktiDukung->map(function ($item) {
                return [
                    'id' => encrypt($item->id),
                    'file' => $item->file ? Storage::temporaryUrl($item->file, now()->addMinutes(5)) : null,
                ];
            })->toArray(),
        ];

        $edit = true;
        return view('Pages.LaporanKegiatan.form', compact('edit', 'data'));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        $data = LaporanKegiatan::where('id', decrypt($request->id))
            ->lockForUpdate()
            ->first();

        $request->validate([
            'file' => [
                Rule::requiredIf(fn() => empty($data->file)),
                File::types(['pdf'])->max(2 * 1024),
            ],
            'file_bukti_kehadiran' => [
                Rule::requiredIf(fn() => empty($data->file_bukti_kehadiran)),
                // File::types(['jpg', 'png', 'jpeg'])->max(2 * 1024),
                File::types(['pdf'])->max(2 * 1024),
            ],
            'file_bukti_dukung'   => 'array',
            'file_bukti_dukung.*' => File::types(['jpg', 'png', 'jpeg'])->max(2 * 1024),
        ], [
            'file.required' => "File laporan kegiatan wajib diisi",
            'file.max' => "Ukuran laporan kegiatan maksimal 2 MB",
            'file_bukti_kehadiran.required' => "File bukti kehadiran wajib diisi",
            'file_bukti_kehadiran.max' => "Ukuran bukti kehadiran maksimal 2 MB",
            'file_bukti_dukung.*.file'  => 'Setiap bukti dukung harus berupa file pada bukti dukung',
            'file_bukti_dukung.*.mimes' => 'Format file hanya boleh JPG, JPEG, atau PNG pada bukti dukung',
            'file_bukti_dukung.*.max'   => 'Ukuran file maksimal 2 MB per file pada bukti dukung',

        ]);
        $file = null;
        try {

            $this->validateProposalOwnership($data->proposal);
            $this->validateLaporanKegiatanIsEditable($data);
            $this->validateProposalIsEditable($data, 'laporan kegiatan');

            $fileBuktiKehadiran = null;
            $fileBuktiDukung = [];

            $oldFile = $data->file;
            $oldFileBuktiKehadiran = $data->file_bukti_kehadiran;
            if ($request->file('file')) {
                $file = $this->storageStore($request->file('file'), 'laporan-kegiatan');
            }

            if ($request->file('file_bukti_kehadiran')) {
                $fileBuktiKehadiran = $this->storageStore($request->file('file_bukti_kehadiran'), 'bukti-kehadiran');
            }

            if ($request->file('file_bukti_dukung')) {
                foreach ($request->file('file_bukti_dukung') as $fileBukti) {
                    $fileBuktiDukung[] = [
                        'laporan_kegiatan_id' => $data->id,
                        'file' => $this->storageStore($fileBukti, 'bukti-dukung'),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                BuktiDukung::insert($fileBuktiDukung);
            }


            $data->fill([
                'file' => $file ?? $data->file,
                'file_bukti_kehadiran' => $fileBuktiKehadiran ?? $data->file_bukti_kehadiran,
                'status' => 'Draft',
            ]);
            $this->updateLog($data, 'Merubah Laporan Kegiatan', 'Laporan Kegiatan');
            if ($request->file('file')) {
                $this->storageDelete($oldFile);
            }
            if ($request->file('file_bukti_kehadiran')) {
                $this->storageDelete($oldFileBuktiKehadiran);
            }

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $this->getUpdateSuccessMessage(),
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();
            $this->storageDelete($file);
            $this->storageDelete($file);

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
            $data = LaporanKegiatan::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalOwnership($data->proposal, 'laporan kegiatan');
            $this->validateLaporanKegiatanIsEditable($data);
            $this->validateProposalIsEditable($data, 'laporan kegiatan');

            $dosen = Dosen::where('id', $data->proposal->dosen_id)
                ->lockForUpdate()
                ->first();

            $notifikasi = new NotifikasiController();
            $message = $dosen ? $notifikasi->generateMessageForVerifikator(jenisPengajuan: 'Laporan Kegiatan', nama: $dosen->name, judulKegiatan: $data->proposal->name, unitKemahasiswaan: $data->proposal->pengusul->name, route: route('approval-laporan-kegiatan.edit', encrypt($data->id))) : '-';

            $response = $notifikasi->sendMessage($dosen->no_hp ?? 0, $message, 'Dosen Penanggung Jawab', 'Pengajuan');

            $data->fill([
                'status' => 'Pending Dosen',
            ]);

            $desc =  $admin ?  'Admin telah mengajukan laporan kegiatan anda' : 'Berhasil mengajukan laporan kegiatan';


            $this->updateLog($data, $desc, 'Laporan Kegiatan');

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => $response,
            ], 200);
        } catch (Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 400,
                'message' =>  $this->getErrorMessage($e),
            ], 400);
        }
    }

    public function deleteImage(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = BuktiDukung::where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalOwnership($data->LaporanKegiatan->proposal);
            $this->validateLaporanKegiatanIsEditable($data);
            $this->validateProposalIsEditable($data->LaporanKegiatan, 'laporan kegiatan');

            $this->storageDelete($data->file);

            $data->delete();

            DB::commit();
            return response()->json([
                'status' => 200,
                'message' => 'Gambar Berhasil Dihapus',
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 400,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function getData(Request $request)
    {
        $data = collect();
        $admin = Gate::allows('admin');
        $unitKemahasiswaan = $this->validateUserIsUnitKemahasiswaan(Auth::user());
        $now = Carbon::now();


        if ($admin || $unitKemahasiswaan) {
            $data = LaporanKegiatan::with([
                'proposal:id,name,no_proposal,unit_id,mahasiswa_id,dosen_id',
                'proposal.dosen:id,name',
                'proposal.pengusul:id,name,jurusan_id',
                'proposal.pengusul.jurusan:id,name',
                'proposal.ketua:id,jurusan_id,name,npm',
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
        } else {
            $data = new LengthAwarePaginator([], 0, $request->itemDisplay ?? 10);
        }

        $dataFormated = $data->map(function ($item) use ($admin) {
            return [
                'id' => encrypt($item->id),
                'status' => $item->status,
                'name' => $item->proposal->name,
                'dosen' => $item->proposal->dosen->name,
                'ketua' => $item->proposal->ketua->name,
                'npm_ketua' => $item->proposal->ketua->npm,
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
