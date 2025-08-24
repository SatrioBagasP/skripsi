<?php

namespace App\Traits;

use Exception;
use App\Models\User;
use App\Models\Jurusan;

trait UserValidation
{
    use CommonValidation;

    public function validateUserAlreadyHasAccount($id, $type)
    {
        $hasUser = User::where('userable_type', $type)
            ->where('userable_id', $id)
            ->exists();

        if ($hasUser) {
            throw new \Exception('Organisasi / Dosen sudah memiliki akun');
        }
    }
    // Model should class of userable either UnitKemahasiswaan:class, or Dosen::class
    public function validateUserAbleIsActive($id, $model)
    {
        $model = new $model;
        $userAble = $model->where('id', $id)
            ->lockForUpdate()
            ->first();
        if ($userAble->status == 0) {
            throw new \Exception('Organisasi / Dosen Yang Dipilih Tidak Aktif');
        }
    }
}
