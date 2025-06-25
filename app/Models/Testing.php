<?php
// untuk gambar skripsi
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;


// class User extends Authenticatable
// {
//     protected $table = 'users';
//     protected $fillable = [
//         'name',
//         'roles_id',
//         'userable_id',
//         'userable_type',
//         'email',
//         'password',
//     ];
    
//     public function proposal(){
//         return $this->hasMany(Proposal::class,'user_id','id');
//     }

//     public function roles(){
//         return $this->belongsTo(Roles::class,'roles_id','id');
//     }

//     public function userable()
//     {
//         return $this->morphTo();
//     }
// }
