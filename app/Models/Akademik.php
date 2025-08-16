<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akademik extends Model
{
    protected $table = 'akademik';

    protected $fillable = [
        'ketua_id',
        'name',
        'no_hp',
        'status',
    ];

    public function ketua()
    {
        return $this->belongsTo(Dosen::class, 'ketua_id', 'id');
    }
}
