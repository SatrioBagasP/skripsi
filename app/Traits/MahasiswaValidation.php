<?php

namespace App\Traits;

use Exception;
use App\Models\Mahasiswa;

trait MahasiswaValidation
{
    use CommonValidation;

    public function validateMahasiswaIsActive($id)
    {
        $data = Mahasiswa::where('id', $id)
            ->lockForUpdate()
            ->first();

        $this->validateExistingData($data);

        if ($data->status != 1) {
            throw new Exception('Mahasiswa tidak aktif!');
        }
    }
}
