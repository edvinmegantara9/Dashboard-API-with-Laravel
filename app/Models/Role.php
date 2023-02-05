<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
    ];

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_menus', 'role_id', 'menu_id');
    }
}