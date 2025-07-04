<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKemahasiswaan extends Model
{
    protected $table = 'unit_kemahasiswaan';
    protected $fillable = [
        'name',
        'image',
        'no_hp',
        'status',
        'jurusan_id',
    ];

    public function user(){
        return $this->morphOne(User::class,'userable');
    }

    public function jurusan(){
        return $this->belongsTo(Jurusan::class,'jurusan_id','id');
    }
}
