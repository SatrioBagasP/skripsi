<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'name',
    ];

    public function user()
    {
        return $this->belongsToMany(User::class, 'user_has_role', 'role_id', 'user_id');
    }
}
