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
    ];

    public function user(){
        return $this->morphOne(User::class,'userable');
    }
    public function proposal(){
        return $this->hasMany(Proposal::class,'dosen_id','id');
    }
}
