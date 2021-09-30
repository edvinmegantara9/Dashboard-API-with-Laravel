<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends Model
{
    use SoftDeletes;


    public function user()
    {
        return $this->belongsTo(Users::class, 'sender_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Roles::class, 'created_by', 'id');
    }

    public function receivers()
    {
        return $this->belongsToMany(Roles::class,'message_receivers', 'message_id', 'receiver_id');
    }
}
