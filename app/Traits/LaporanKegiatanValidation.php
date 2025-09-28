<?php

namespace App\Traits;

use Exception;
use Carbon\Carbon;
use App\Models\Jurusan;

trait LaporanKegiatanValidation
{
    public function validateLaporanKegiatanIsEditable($laporanKegiatan, $return = 'Exception')
    {
        $now = Carbon::now();
        if ($laporanKegiatan->available_at >= $now) {
            $message = 'Belum waktunya untuk melakukan laporan kegiatan!';
            if ($return == 'Exception') {
                throw new Exception($message);
            } elseif ($return == 'Abort') {
                abort(403, $message);
            } elseif ($return == 'Boolean') {
                return false;
            } else {
                dd('BA!');
            }
        } else {
            return true;
        }
    }
}
