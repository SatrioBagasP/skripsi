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
    ];

    public function user(){
        return $this->morphOne(User::class,'userable');
    }
}
