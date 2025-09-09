<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKegiatan extends Model
{
    protected $table = 'laporan_kegiatan';
    protected $fillable = [
        'proposal_id',
        'file',
        'file_bukti_kehadiran',
        'available_at',
        'status',
        'alasan_tolak',
    ];

    public function buktiDukung()
    {
        return $this->hasMany(BuktiDukung::class, 'laporan_kegiatan_id', 'id');
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class, 'proposal_id', 'id');
    }
}
