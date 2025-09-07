<?php

namespace App\Traits;

use Exception;
use App\Models\User;
use App\Models\Jurusan;
use App\Models\Akademik;
use Illuminate\Support\Facades\DB;

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

    public function validateUserIsDosen($user)
    {
        if ($user->userable_type === 'App\\Models\\Dosen') {
            return true;
        } else {
            return false;
        }
    }

    public function validateUserIsKetuaMinatBakat($user)
    {
        $dosen = $this->validateUserIsDosen($user);

        if ($dosen) {
            $ketuaMinatDanBakat = Akademik::where('id', 2) // id dari akademik
                ->where('ketua_id', $user->userable_id)
                ->exists();
            return $ketuaMinatDanBakat;
        } else {
            return false;
        }
    }

    public function validateUserIsLayananMahasiswa($user)
    {
        $dosen = $this->validateUserIsDosen($user);

        if ($dosen) {
            $layananMahasiswa = DB::table('dosen_has_akademik')
                ->where('akademik_id', 1) // id dar akademik layanan
                ->where('dosen_id', $user->userable_id)
                ->exists();

            return $layananMahasiswa;
        } else {
            return false;
        }
    }

    public function validateUserIsWakilRektor1($user)
    {
        $dosen = $this->validateUserIsDosen($user);

        if ($dosen) {
            $wakilRektor = $user->userable->jabatan_id == 3; // id jabatan wakil rektor

            return $wakilRektor;
        } else {
            return false;
        }
    }

    public function validateUserIsKaprodi($user)
    {
        $dosen = $this->validateUserIsDosen($user);
        if ($dosen) {
            $kaprodi = Jurusan::where('ketua_id', $user->userable_id)
                ->exists();

            return $kaprodi;
        } else {
            return false;
        }
    }
}
