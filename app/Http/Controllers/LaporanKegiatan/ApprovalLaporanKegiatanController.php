<?php

namespace App\Http\Controllers\LaporanKegiatan;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use App\Traits\UserValidation;
use App\Models\LaporanKegiatan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Traits\ApprovalProposalValidation;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Notifikasi\NotifikasiController;

class ApprovalLaporanKegiatanController extends Controller
{

    use UserValidation, ApprovalProposalValidation;

    public function index()
    {
        $head = ['No Proposal', 'Nama', 'Ketua Pelaksana', 'Dosen', 'Organisasi',  'Jurusan', 'Status', 'Aksi'];
        // $admin = Gate::allows('admin');
        // if ($admin) {
        //     $head[] = 'Dosen';
        //     $head[] = 'Jurusan';
        // }

        // $head[] = 'Status';
        // $head[] = 'Aksi';
        return view('Pages.LaporanKegiatan.approval-index', compact('head'));
    }

    public function edit(Request $request, $id)
    {
        $data = LaporanKegiatan::with([
            'proposal:id,name,no_proposal,unit_id,dosen_id,mahasiswa_id',
            'buktiDukung:id,laporan_kegiatan_id,file',
        ])
            ->where('id', decrypt($id))
            ->first();


        $this->validateExistingDataReturnAbort($data);
        $this->validateProposalIsApproveable($data, true, 'laporan kegiatan');
        $this->validateUserCanApprove($data->proposal, true, 'laporan kegiatan');


        $data = [
            'id' => encrypt($data->id),
            'name' => $data->proposal->no_proposal . ' - ' . $data->proposal->name,
            'ketua_id' => $data->proposal->mahasiswa_id,
            'file' => $data->file ? Storage::temporaryUrl($data->file, now()->addMinutes(5)) : null,
            'file_bukti_kehadiran' => $data->file_bukti_kehadiran ? Storage::temporaryUrl($data->file_bukti_kehadiran, now()->addMinutes(5)) : null,
            'status' => $data->status,
            'bukti_dukung' => $data->buktiDukung->map(function ($item) {
                return [
                    'file' => $item->file ? Storage::temporaryUrl($item->file, now()->addMinutes(5)) : null,
                ];
            })->toArray(),
            'mahasiswa' => $data->proposal->mahasiswa,
            'approvalBtn' => $this->getApprovalButton($data),
            'approvalUrl' => $this->getUrlApproval($data),
        ];

        $data['mahasiswa'] = $data['mahasiswa']->sortByDesc(function ($mhs) use ($data) {
            return $mhs->id == $data['ketua_id']; // ketua akan jadi true (1), lainnya false (0)
        });

        return view('Pages.LaporanKegiatan.approval', compact('data'));
    }

    public function accLaporanKegiatan($laporanKegiatan, $status, $desc, $reciever, $nextVerifikator, $messageFor)
    {
        try {
            $laporanKegiatan->fill([
                'status' => $status,
            ]);
            $this->updateLog($laporanKegiatan, $desc, 'Laporan Kegiatan');

            $notifikasi = new NotifikasiController();
            if ($messageFor == 'Pengajuan') {
                $message = $notifikasi->generateMessageForVerifikator(jenisPengajuan: 'Laporan Kegiatan', nama: $reciever->name ?? '-', judulKegiatan: $laporanKegiatan->proposal->name, unitKemahasiswaan: $laporanKegiatan->proposal->pengusul->name, route: route('approval-laporan-kegiatan.edit', encrypt($laporanKegiatan->id)));
            } elseif ($messageFor == 'Diterima') {
                $message = $notifikasi->generateMessageForDoneFinal(jenisPengajuan: 'Laporan Kegiatan', judulKegiatan: $laporanKegiatan->proposal->name, ketua: $reciever->name);
            }
            $message = $reciever ? $message : '-';
            $noHp = $reciever->no_hp ?? 0;

            $response = $notifikasi->sendMessage($noHp, $message, $nextVerifikator, $messageFor);
            return $response;
        } catch (Throwable $e) {
            throw new Exception($this->getErrorMessage($e));
        }
    }

    public function rejectLaporanKegiatan($laporanKegiatan, $status, $reason, $desc)
    {
        try {
            $laporanKegiatan->fill([
                'status' => $status,
                'alasan_tolak' => $reason,
            ]);
            $this->updateLog($laporanKegiatan, $desc, 'Proposal');

            $notifikasi = new NotifikasiController();
            $noHp = $laporanKegiatan->proposal->ketua->no_hp;

            $message = $notifikasi->generateMessageForRejected(jenisPengajuan: 'Laporan Kegiatan', alasanTolak: $reason, judulKegiatan: $laporanKegiatan->proposal->name, unitKemahasiswaan: $laporanKegiatan->proposal->pengusul->name, route: route('laporan-kegiatan.edit', encrypt($laporanKegiatan->proposal->id)));
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
            $data = LaporanKegiatan::with([
                'proposal.ketua:id,name,no_hp',
                'proposal.pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();


            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data, null, 'laporan kegiatan');
            $this->validateApprovalDosen($data, 'laporan kegiatan');

            $nonJurusan = $data->proposal->pengusul->is_non_jurusan == true;
            $status = $nonJurusan ? 'Pending Layanan Mahasiswa' : 'Pending Kaprodi';

            if ($request->boolean('approve')) {
                // jika dia buka jurusan, maka langsung ambil layanan mahasiswanya siapa
                $jurusanId = $data->proposal->pengusul->jurusan_id;
                $reciever = $nonJurusan ? $this->getLayananMahasiswa() : $this->getKaprodi($jurusanId);
                $nextVerifikator = $nonJurusan ? 'Layanan Mahasiswa' : 'Kaprodi';
                $response = $this->accLaporanKegiatan($data, $status, 'Dosen Telah Menyetujui Laporan Kegiatan Anda!', $reciever, $nextVerifikator, 'Pengajuan');
            } else {
                $response = $this->rejectLaporanKegiatan($data, 'Rejected', $request->reason, 'Dosen Telah Menolak Laporan Kegiatan Anda Anda dengan alasan ' . $request->reason);
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
            $data = LaporanKegiatan::with([
                'proposal.ketua:id,name,no_hp',
                'proposal.pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data, null, 'laporan kegiatan');
            $this->validateApprovalKaprodi($data, 'laporan kegiatan');

            if ($request->boolean('approve')) {
                // $reciever = $this->getKepalaBagianMinatBakat();
                $reciever = $this->getMinatBakat();
                $response =  $this->accLaporanKegiatan($data, 'Pending Minat dan Bakat', 'Kaprodi Telah Menyetujui Laporan Kegiatan Anda!', $reciever, 'Akademik Minat dan Bakat', 'Pengajuan');
            } else {
                $response =  $this->rejectLaporanKegiatan($data, 'Rejected', $request->reason, 'Kaprodi Telah Menolak Pengajuan Laporan Kegiatan Anda dengan alasan ' . $request->reason);
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
            $data = LaporanKegiatan::with([
                'proposal.ketua:id,name,no_hp',
                'proposal.pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data, null, 'laporan kegiatan');
            $this->validateApprovalKetuaMinatBakat($data);

            if ($request->boolean('approve')) {
                $reciever = $this->getLayananMahasiswa();
                $response = $this->accLaporanKegiatan($data, 'Pending Layanan Mahasiswa', 'Kepala Bagian Minat dan Bakat Telah Menyetujui Laporan Kegiatan Anda!', $reciever, 'Layanan Mahasiswa', 'Pengajuan');
            } else {
                $response =  $this->rejectLaporanKegiatan($data, 'Rejected', $request->reason, 'Kepala Bagian Minat dan Bakat Telah Menolak Pengajuan Laporan Kegiatan Anda dengan alasan ' . $request->reason);
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
            $data = LaporanKegiatan::with([
                'proposal.ketua:id,name,no_hp',
                'proposal.pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data, null, 'laporan kegiatan');
            $this->validateApprovalLayananMahasiswa($data);

            if ($request->boolean('approve')) {
                $reciever = $this->getWakilRektor1();
                $response = $this->accLaporanKegiatan($data, 'Pending Wakil Rektor 1', 'Layanan Mahasiswa Menyetujui Laporan Kegiatan Anda!', $reciever, 'Wakil Rektor 1', 'Pengajuan');
            } else {
                $response =  $this->rejectLaporanKegiatan($data, 'Rejected', $request->reason, 'Layanan Mahasiswa Telah Menolak Pengajuan Laporan Kegiatan Anda dengan alasan ' . $request->reason);
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
            $data = LaporanKegiatan::with([
                'proposal.ketua:id,name,no_hp',
                'proposal.pengusul' => function ($q) {
                    $q->lockForUpdate();
                },
            ])
                ->where('id', decrypt($request->id))
                ->lockForUpdate()
                ->first();

            $this->validateExistingDataReturnException($data);
            $this->validateProposalIsApproveable($data, null, 'laporan kegiatan');
            $this->validateApprovalWakilRektor($data);

            if ($request->boolean('approve')) {
                $reciever = $data->proposal->ketua;
                $response =  $this->accLaporanKegiatan($data, 'Accepted', 'Wakil Rektor 1 Menyetujui Laporan Kegiatan Anda!', $reciever, 'Ketua Pelaksana', 'Diterima');
            } else {
                $response =  $this->rejectLaporanKegiatan($data, 'Rejected', $request->reason, 'Wakil Rektor 1 Telah Menolak Pengajuan Laporan Kegiatan Anda dengan alasan ' . $request->reason);
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

    public function getUrlApproval($laporanKegiatan)
    {
        if ($laporanKegiatan->status == 'Pending Dosen') {
            return route('approval-laporan-kegiatan.approvalDosen');
        } elseif ($laporanKegiatan->status == 'Pending Kaprodi') {
            return route('approval-laporan-kegiatan.approvalKaprodi');
        } elseif ($laporanKegiatan->status == 'Pending Minat dan Bakat') {
            return route('approval-laporan-kegiatan.approvalMinatBakat');
        } elseif ($laporanKegiatan->status == 'Pending Layanan Mahasiswa') {
            return route('approval-laporan-kegiatan.approvalLayananMahasiswa');
        } elseif ($laporanKegiatan->status == 'Pending Wakil Rektor 1') {
            return route('approval-laporan-kegiatan.approvalWakilRektor');
        } else {
            return null;
        }
    }

    public function getApprovalButton($data)
    {
        $admin = Gate::allows('admin');
        $user = Auth::user()->load('userable.jurusan');

        $dosen = $this->validateUserIsDosen($user);
        $ketuaMinatDanBakat = $this->validateUserIsKetuaMinatBakat($user);
        $layananMahasiswa = $this->validateUserIsLayananMahasiswa($user);
        $wakilRektor = $this->validateUserIsWakilRektor1($user);
        $kaprodi = $this->validateUserIsKaprodi($user);
        $dosenPj = $dosen ? $data->proposal->dosen_id == Auth::user()->userable_id : false;


        if (($data->status == 'Rejected' || $data->status == 'Draft' || $data->status == 'Accepted') && ($dosenPj || $admin)) {
            return false;
        } elseif ($data->status == 'Pending Dosen' && ($dosenPj || $admin)) {
            return true;
        } elseif ($data->status == 'Pending Kaprodi' && ($kaprodi || $admin)) {
            return true;
        } elseif ($data->status == 'Pending Minat dan Bakat' && ($ketuaMinatDanBakat || $admin)) {
            return true;
        } elseif ($data->status == 'Pending Layanan Mahasiswa' && ($layananMahasiswa || $admin)) {
            return true;
        } elseif ($data->status == 'Pending Wakil Rektor 1' && ($wakilRektor || $admin)) {
            return true;
        } else {
            return false;
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

            $data = LaporanKegiatan::with([
                'proposal:id,name,no_proposal,unit_id,mahasiswa_id,dosen_id',
                'proposal.pengusul:id,jurusan_id,name',
                'proposal.pengusul.jurusan:name',
                'proposal.ketua:id,name,jurusan_id,npm',
                'proposal.ketua.jurusan:id,name',
                'proposal.dosen:id,name',
            ])
                ->when($admin == false, function ($q) use ($dosen, $user, $ketuaMinatDanBakat, $layananMahasiswa, $wakilRektor, $kaprodi) {
                    $q->when($dosen == true, function ($q) use ($user) {
                        $q->whereRelation('proposal', 'dosen_id', $user->userable_id)
                            ->whereNotIn('status', ['Draft', 'Rejected', 'Accepted']);
                    })
                        ->when($kaprodi == true, function ($q) use ($user) {
                            $q->orWhere(function ($q) use ($user) {
                                $q->where('status', 'Pending Kaprodi')
                                    ->whereRelation('proposal.pengusul', 'jurusan_id', $user->userable->jurusan_id); // krn jika dia itu tidak memiliki jurusan atau eksul, dia tidak perlu acc kaprodi
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
                        $item->whereRelation('proposal', 'name', 'like', '%' . $request->search . '%')
                            ->orWhereRelation('proposal', 'no_proposal', 'like', '%' . $request->search . '%')
                            ->orWhereRelation('proposal.pengusul', 'name', 'like', '%' . $request->search . '%')
                            ->orWhereRelation('proposal.pengusul.jurusan', 'name', 'like', '%' . $request->search . '%');
                    });
                })
                ->select('id', 'proposal_id', 'status')
                ->whereNotIn('status', ['Draft', 'Rejected', 'Accepted'])
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
                'organisasi' => $item->proposal->pengusul->name,
                'jurusan' => $item->proposal->pengusul->jurusan  ?  $item->proposal->pengusul->jurusan->name : $item->proposal->ketua->jurusan->name,
                'admin' => $admin,
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
