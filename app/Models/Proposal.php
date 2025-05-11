<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $table = 'proposal';
    protected $fillable = [
        'user_id',
        'dosen_id',
        'name',
        'desc',
        'file',
        'is_harian',
        'start_date',
        'end_date',
        'status',
        'alasan_tolak',
        'is_acc_dosen',
        'is_acc_kaprodi',
        'is_acc_minat_bakat',
        'is_acc_layanan',
        'is_acc_wakil_rektor',
    ];

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function dosenPJ(){
        return $this->belongsTo(Dosen::class,'dosen_id','id');
    }

    public function ruangan(){
        return $this->belongsToMany(Ruangan::class,'proposal_has_ruangan','proposal_id','ruangan_id');
    }
}
