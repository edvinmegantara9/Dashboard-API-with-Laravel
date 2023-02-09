<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    //
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function document_considers(){
        return $this->hasMany(DocumentConsider::class, 'document_id', 'id');
    }

    public function document_decisions(){
        return $this->hasMany(DocumentDecision::class, 'document_id', 'id');
    }

    public function document_remembers(){
        return $this->hasMany(DocumentRemember::class, 'document_id', 'id');
    }

    public function document_notices(){
        return $this->hasMany(DocumentNotice::class, 'document_id', 'id');
    }

    public function document_statuses(){
        return $this->hasMany(DocumentStatus::class, 'document_id', 'id');
    }

    public function document_attachments(){
        return $this->hasMany(DocumentAttachment::class, 'document_id', 'id');
    }    
    
}
