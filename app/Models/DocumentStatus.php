<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentStatus extends Model
{
    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
