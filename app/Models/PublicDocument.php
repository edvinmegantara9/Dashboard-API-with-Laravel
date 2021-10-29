<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicDocument extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function document_type()
    {
        return $this->belongsTo(DocumentTypes::class, 'sub_document_type', 'id');
    }
}
