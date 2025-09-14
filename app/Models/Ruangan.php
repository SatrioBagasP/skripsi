<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $fillable = [
        'name',
        'status',
    ];

    public function proposal(){
        return $this->belongsToMany(Proposal::class, 'proposal_has_ruangan', 'ruangan_id', 'proposal_id');
    }
}

