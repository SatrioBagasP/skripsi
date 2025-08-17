<?php

namespace App\Traits;

use Exception;
use App\Models\Jurusan;

trait JurusanValidation
{
    use CommonValidation;

    public function validateJurusanIsActive($id)
    {
        $data = Jurusan::where('id', $id)
            ->lockForUpdate()
            ->first();

        $this->validateExistingData($data);

        if ($data->status != 1) {
            throw new Exception('Jurusan tidak aktif!');
        }
    }
}
