<?php

namespace App\Traits;

use Exception;
use App\Models\Jurusan;

trait JurusanValidation
{
    use DosenValidation;

    public function validateJurusanIsActive($id)
    {
        $data = Jurusan::where('id', $id)
            ->lockForUpdate()
            ->first();

        $this->validateExistingDataReturnException($data);

        if ($data->status != 1) {
            throw new Exception('Jurusan tidak aktif!');
        }
    }

    public function validateKetuaJurusan($ketuaId)
    {
        $this->validateDosenIsActive($ketuaId);

        $data = Jurusan::where('ketua_id', $ketuaId)
            ->lockForUpdate()
            ->first();

        if ($data) {
            throw new Exception('Dosen sudah telah menjadi ketua jurusan lain');
        }
    }
}
