<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiDukung extends Model
{
    protected $table = 'bukti_dukung';
    protected $fillable = [
        'laporan_kegiatan_id',
        'file',
    ];

    public function laporanKegiatan(){
        return $this->belongsTo(laporanKegiatan::class,'laporan_kegiatan_id','id');
    }
}
