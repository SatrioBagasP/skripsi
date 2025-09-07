<?php

namespace App\Traits;

use Exception;

trait CommonValidation
{
    public function validateExistingDataReturnException($data, $message = null)
    {
        if (!$data) {
            $message = $message ? $message : 'Data tidak ditemukan silahkan refresh halaman ini';
            throw new Exception($message);
        }
    }

    public function validateExistingDataReturnAbort($data, $message = null)
    {
        if (!$data) {
            $message = $message ? $message : 'Data tidak ditemukan!';
            abort(404, $message);
        }
    }

    public function validateRangeDate($request) {}
}
