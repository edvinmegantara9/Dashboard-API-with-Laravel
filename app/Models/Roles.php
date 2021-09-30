<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Roles extends Model
{
    use SoftDeletes;
    use HasFactory;
    
    protected $guarded = [];

    public function users()
    {
        return $this->hasMany(Users::class);
    }

    public function opd() 
    {
        return $this->belongsToMany(Roles::class, 'roles_opds', 'role_id', 'opd_id');
    }

}
