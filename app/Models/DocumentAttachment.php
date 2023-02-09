<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentAttachment extends Model
{
    public function documents()
    {
        return $this->belongsTo(Document::class, 'document_id', 'id');
    }
}
