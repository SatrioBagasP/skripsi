<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitKemahasiswaan extends Model
{
    protected $table = 'unit_kemahasiswaan';
    protected $fillable = [
        'name',
        'image',
        'is_non_jurusan',
        'status',
        'jurusan_id',
    ];

    public function proposal()
    {
        return $this->hasMany(Proposal::class, 'unit_id', 'id');
    }

    public function user()
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id');
    }
}
