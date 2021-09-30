<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageReceivers extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'receiver_id',
        'message_id'
    ];

    public function receiver() 
    {
        return $this->belongsTo(Roles::class, 'receiver_id', 'id');
    }

    public function message()
    {
        return $this->belongsTo(Messages::class, 'message_id', 'id');
    }
}
