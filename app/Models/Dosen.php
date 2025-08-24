<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosen';
    protected $fillable = [
        'nip',
        'name',
        'alamat',
        'no_hp',
        'status',
        'jurusan_id',
        'jabatan_id',
    ];

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function proposal()
    {
        return $this->hasMany(Proposal::class, 'dosen_id', 'id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id', 'id');
    }

    public function akademik()
    {
        return $this->belongsToMany(Akademik::class, 'dosen_has_akademik', 'dosen_id', 'akademik_id')->where('akademik.status', true);
    }
}
