<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessageAttachments extends Model
{
    // use SoftDeletes;

    protected $guarded = [];

    public function message()
    {
        return $this->belongsTo(MessageAttachments::class, 'message_id', 'id');
    }
}
