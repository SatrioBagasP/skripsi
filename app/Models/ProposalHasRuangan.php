<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalHasRuangan extends Model
{
    protected $table = 'proposal_has_ruangan';
    protected $fillable = [
        'ruangan_id',
        'proposal_id',
    ];

    public function proposal(){
        return $this->belongsTo(Proposal::class,'proposal_id','id');
    }

    public function ruangan(){
        return $this->belongsTo(Ruangan::class,'ruangan_id','id');
    }
}
