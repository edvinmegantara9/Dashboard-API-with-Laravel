<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chats extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $table = 'rooms';

    public function user()
    {
        return $this->belongsTo(Roles::class, 'created_by', 'id');
    }

    public function room_receivers()
    {
        return $this->hasMany(ChatsReceivers::class, 'room_id', 'id');
    }

    public function receivers()
    {
        return $this->belongsToMany(Roles::class, 'room_receivers', 'room_id', 'role_id');
    }
}
