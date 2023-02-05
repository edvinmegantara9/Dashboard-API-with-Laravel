<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function menus()
    {
        return $this->belongsTo(menus::class, 'menu_id', 'id');
    }
}
