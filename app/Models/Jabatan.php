<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    protected $table = 'jabatan';
    protected $fillable = [
        'name',
    ];

    public function dosen()
    {
        return $this->hasMany(Dosen::class, 'jabatan_id', 'id');
    }
}
