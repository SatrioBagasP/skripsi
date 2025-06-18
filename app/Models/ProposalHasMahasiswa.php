<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalHasMahasiswa extends Model
{
    protected $table = 'proposal_has_mahasiswa';
    protected $fillable = [
        'mahasiswa_id',
        'proposal_id',
    ];

    public function proposal(){
        return $this->belongsTo(Proposal::class,'proposal_id','id');
    }

    public function mahasiswa(){
        return $this->belongsTo(Mahasiswa::class,'mahasiswa_id','id');
    }
}
