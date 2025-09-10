<?php

namespace App\Traits;

use Exception;
use App\Models\Jurusan;
use App\Models\UnitKemahasiswaan;

trait UnitKemahasiswaanValidation
{
    use CommonValidation;

    public function validateUnitKemahasiswaanHasPendingProposal($data)
    {
        if ($data->proposal->isNotEmpty()) {
            $data->proposal->each(function ($item) {
                if (!in_array($item->status, ['Draft', 'Rejected', 'Accepted'])) {
                    throw new \Exception('Tidak bisa merubah unit kemahasiswaan, dikarenakan ada proposal pada unit ini masih tahap pengecekan oleh verifikator');
                }

                if ($item->laporanKegiatan !== null) {
                    if ($item->laporanKegiatan->status != 'Accepted') {
                        throw new \Exception('Tidak bisa merubah unit kemahasiswaan, dikarenakan ada laporan kegiatan pada unit ini masih memiliki tanggungan laporan kegiatan yang belum selesai');
                    }
                }
            });
        }
    }

    public function validateUnitKemahasiswaanIsActive($id)
    {
        $data = UnitKemahasiswaan::where('id', $id)
            ->lockForUpdate()
            ->first();

        $this->validateExistingDataReturnException($data);

        if ($data->status != 1) {
            throw new Exception('Unit Kemahasiswaan tidak aktif!');
        }

        return $data;
    }
}
