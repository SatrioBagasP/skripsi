<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Models\Dosen;
use App\Models\Proposal;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Helper\CrudController;

trait ProposalRequestValidator
{
    // memvalidasi unit kemahasiswaaan yang dipilih
    public function validateUnitKemahasiswaan($request, $admin)
    {
        if ($admin) {
            $unitKemahasiswaan = UnitKemahasiswaan::where('id', $request->user_id)->first();
        } else {
            $unitKemahasiswaan = Auth::user()->userable;
            if (!($unitKemahasiswaan instanceof UnitKemahasiswaan)) {
                throw new \Exception('Organisasi yang dipilih bukan dari unit kemahasiswaan');
            }
        }
        return $unitKemahasiswaan;
    }

    // memvalidasi date yang dipilih pada proposal
    public function validateDate($request)
    {
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
        return [$startDate, $endDate];
    }

    // membalidasi dosen yang dipilih
    public function validateDosen($id)
    {
        $dosenEligible = Dosen::where('id', $id)
            ->lockForUpdate()
            ->first();

        if ($dosenEligible->status == false) {
            throw new \Exception('Dosen yang anda pilih sudah tidak aktif! Silahkan pilih dosen yang lain');
        }

        return $dosenEligible;
    }

    // memvalidasi proposal untuk membuat proposal semeentara
    public function validateProposal($kodeJurusan, $romawi, $tahun)
    {
        DB::beginTransaction();
        $lastRecord = Proposal::where('no_proposal', 'LIKE', '%/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun)
            ->orderBy('no_proposal', 'desc')
            ->first();

        if ($lastRecord == null) {
            $got = DB::selectOne("SELECT GET_LOCK('nomor_lock', 10)")->{"GET_LOCK('nomor_lock', 10)"};
            if ($got !== 1) {
                throw new \Exception('Server sedang sibuk, Silahkan coba lagi!');
            }
        } else {
            // jika dia ada data nya, maka lock data terlama dulu
            $lastRecord = Proposal::where('no_proposal', 'LIKE', '%/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun)
                ->orderBy('no_proposal', 'desc')
                ->lockForUpdate()
                ->first();
        }
        // jika selesai lock, ambil lagi nomor terakhir , dan nomor tersebut di lock lagi
        $lastRecord = Proposal::where('no_proposal', 'LIKE', '%/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun)
            ->orderBy('no_proposal', 'desc')
            ->lockForUpdate()
            ->first();

        $lastNumber = $lastRecord ? intval(explode('/', $lastRecord->no_proposal)[0]) : 0;
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $noProposal = $newNumber  . '/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun;

        $dataField = [
            'name' => '',
            'desc' => '',
            'no_proposal' => $noProposal,
            'dosen_id' => 1,
            'user_id' => 1,
            'file' => '',
            'is_harian' => '0',
            'status' => 'Temp',
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now(),
        ];

        $Crud = new CrudController(Proposal::class, dataField: $dataField, withLog: false);
        $data = $Crud->insertWithReturnData();

        if ($lastRecord == null) {
            DB::select("SELECT RELEASE_LOCK('bukti_lock')");
        }

        DB::commit();
        return $data;
    }

    // validasi proposal status , untuk update dan delete
    public function validateProposalStatus($request)
    {
        $data = Proposal::with(['mahasiswa'])->where('id', decrypt($request->id))
            ->lockForUpdate()
            ->first();
        if (!$data) {
            throw new \Exception('Data proposal tidak ada atau telah dihapus, silahkan refresh halaman ini atau kembali ke halaman proposal');
        } elseif (!in_array($data->status, ['Draft', 'Tolak'])) {
            throw new \Exception('Tidak bisa merubah data proposal karena sudah diajukan');
        }

        return $data;
    }

    public function validatePengajuanProposalStatus($request)
    {
        $data = Proposal::where('id', decrypt($request->id))
            ->lockForUpdate()
            ->first();

        if (!$data) {
            throw new \Exception('Data proposal tidak ada atau telah dihapus, silahkan refresh halaman ini');
        } elseif (!in_array($data->status, ['Draft', 'Tolak'])) {
            throw new \Exception('Tidak bisa mengajukan data proposal karena sudah proposal ini sudah diajukan');
        }

        return $data;
    }
}
