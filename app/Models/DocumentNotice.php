<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNotice extends Model
{
    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }
}
