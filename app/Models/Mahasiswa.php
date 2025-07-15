<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';
    protected $fillable = [
        'name',
        'npm',
        'no_hp',
        'jurusan_id',
        'status'
    ];

    public function jurusan(){
        return $this->belongsTo(Jurusan::class,'jurusan_id','id');
    }
}
