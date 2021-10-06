<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolesOpds extends Model
{
    use SoftDeletes;
    
    protected $guard = [];

    public function role()
    {
        return $this->belongsTo(Roles::class, 'role_id', 'id');
    }

    public function opd()
    {
        return $this->belongsTo(Roles::class, 'opd_id', 'id');
    }
}
