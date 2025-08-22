<?php

namespace App\Traits;

use Exception;
use App\Models\Jurusan;

trait UnitKemahasiswaanValidation
{
    use CommonValidation;

    public function validateUnitKemahasiswaanHasPendingProposal($data)
    {
        if ($data->user && $data->user->proposal->isNotEmpty()) {
            $data->user->proposal->each(function ($item) {
                if (!in_array($item->status, ['Draft', 'Rejected', 'Accepted'])) {
                    throw new \Exception('Tidak bisa merubah unit kemahasiswaan, dikarenakan ada proposal pada unit ini masih tahap pengecekan oleh verifikator');
                }
            });
        }
    }
}
