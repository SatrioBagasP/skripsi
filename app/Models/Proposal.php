<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $table = 'proposal';
    protected $fillable = [
        'unit_id',
        'mahasiswa_id',
        'dosen_id',
        'no_proposal',
        'name',
        'desc',
        'file',
        'is_harian',
        'start_date',
        'end_date',
        'status',
        'alasan_tolak',
    ];

    public function pengusul()
    {
        return $this->belongsTo(UnitKemahasiswaan::class, 'unit_id', 'id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'id');
    }

    public function ruangan()
    {
        return $this->belongsToMany(Ruangan::class, 'proposal_has_ruangan', 'proposal_id', 'ruangan_id');
    }

    public function ketua()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'id');
    }

    public function mahasiswa()
    {
        return $this->belongsToMany(Mahasiswa::class, 'proposal_has_mahasiswa', 'proposal_id', 'mahasiswa_id')->orderBy('proposal_has_mahasiswa.mahasiswa_id', 'desc');
    }
}
