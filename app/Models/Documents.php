<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documents extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'upload_by',
        'document_type'
    ];

    public function document_type()
    {
        return $this->belongsTo(DocumentTypes::class, 'document_type', 'id');
    }

    public function uploader()
    {
        return $this->belongsTo(Roles::class, 'upload_by', 'id');
    }
}
