<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKegiatan extends Model
{
    protected $table = 'laporan_kegiatan';
    protected $fillable = [
        'proposal_id',
        'file',
        'available_at',
        'status',
        'alasan_tolak',
        'is_acc_dosen',
        'is_acc_kaprodi',
        'is_acc_minat_bakat',
        'is_acc_layanan',
        'is_acc_wakil_rektor',
    ];

    public function buktiDukung(){
        return $this->hasMany(BuktiDukung::class,'laporan_kegiatan_id','id');
    }
}
