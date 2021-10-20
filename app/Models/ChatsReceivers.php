<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatsReceivers extends Model
{
    // use SoftDeletes;

    protected $guarded = [];

    protected $table = 'room_receivers';

    public function role()
    {
        return $this->belongsTo(Chats::class, 'room_id', 'id');
    }
}
