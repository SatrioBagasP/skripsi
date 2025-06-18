<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'jurusan';
    protected $fillable = [
        'name',
        'kode',
        'status'
    ];

    public function dosen(){
        return $this->hasMany(Dosen::class,'jurusan_id','id');
    }

    public function mahasiswa(){
        return $this->hasMany(Mahasiswa::class,'jurusan_id','id');
    }

    public function unitKemahasiswaan(){
        return $this->hasMany(unitKemahasiswaan::class,'jurusan_id','id');
    }
}
