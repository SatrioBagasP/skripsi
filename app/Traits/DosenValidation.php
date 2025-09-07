<?php

namespace App\Traits;

use App\Models\Dosen;
use Exception;

trait DosenValidation
{
    use CommonValidation;

    public function validateDosenIsActive($id)
    {
        $data = Dosen::where('id', $id)
            ->lockForUpdate()
            ->first();

        $this->validateExistingDataReturnException($data);

        if ($data->status != 1) {
            throw new Exception('Dosen tidak aktif!');
        }
        return $data;
    }
}
