<?php

namespace App\Traits;

use Exception;
use Carbon\Carbon;
use App\Models\Proposal;
use App\Models\UnitKemahasiswaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait ProposalValidation
{
    use CommonValidation;

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

    public function validateNomorProposal($kodeJurusan, $romawi, $tahun)
    {
        $lastRecord = Proposal::where('no_proposal', 'LIKE', '%/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun)
            ->lockForUpdate()
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
        return $newNumber  . '/' . $kodeJurusan . '/PR/' . $romawi . '/' . $tahun;
    }

    public function validateProposalIsEditable($data, $type = 'proposal')
    {
        if (!in_array($data->status, ['Draft', 'Rejected'])) {
            throw new \Exception('Tidak bisa merubah data ' . $type . ' karena sudah diajukan');
        }
    }

    public function validateProposalOwnership($data)
    {
        if ((Auth::user()->userable_type == UnitKemahasiswaan::class && $data->unit_id == Auth::user()->userable_id) || Gate::allows('admin')) {
            return;
        } else {
            throw new \Exception('Anda tidak memiliki akses ke proposal ini');
        }
    }
}
