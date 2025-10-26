<?php

namespace App\Traits;

use Exception;
use App\Models\Jurusan;
use App\Models\Ruangan;
use App\Models\Proposal;
use App\Models\UnitKemahasiswaan;

trait RuanganValidation
{
    use CommonValidation;

    public function validateRuanganIsAvailable($ruanganId, $startDate, $endDate)
    {
        $ruangan = Ruangan::query()
            ->whereIn('id', $ruanganId)
            ->lockForUpdate()
            ->with(['proposal' => function ($q) use ($startDate, $endDate) {
                $q->whereNotIn('status', ['Draft', 'Rejected'])
                    ->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            }])
            ->get();

        [$inactiveRuangan, $activeRuangan] = $ruangan->partition(fn($r) => $r->status == false);
        if ($inactiveRuangan->isNotEmpty()) {
            $ruanganList = $inactiveRuangan->pluck('name')->join(', ');
            throw new \Exception("Tidak dapat melanjutkan, ruangan berikut sedang nonaktif: {$ruanganList}.  Silakan pilih ruangan lain.");
        }

        // Cek bentrok jadwal
        $conflictRuangan = $activeRuangan->filter(fn($r) => $r->proposal->isNotEmpty());

        if ($conflictRuangan->isNotEmpty()) {
            $ruanganList = $conflictRuangan->pluck('name')->join(', ');
            throw new \Exception("Tidak dapat melanjutkan, jadwal bentrok dengan ruangan berikut: {$ruanganList}. Silakan pilih ruangan lain.");
        }


        if ($conflictRuangan->isNotEmpty()) {
            $ruanganList = $conflictRuangan->pluck('name')->join(', ');

            throw new \Exception("Tidak dapat melanjutkan, jadwal bentrok dengan ruangan berikut: {$ruanganList}. Silakan pilih ruangan lain.");
        }
    }
}
