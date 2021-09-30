<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chats extends Model
{
    use SoftDeletes;

    public function user()
    {
        return $this->belongsTo(Roles::class, 'created_by', 'id');
    }

    public function receivers()
    {
        return $this->belongsToMany(Roles::class, 'chats_receivers', 'chat_id', 'role_id');
    }
}
