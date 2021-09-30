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
}
