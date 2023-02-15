<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    
    protected $fillable = [
    'legal_drafter',
    'admin_verified',
    'legal_drafter_verified',
    'suncang_verified',
    'kasubag_verified','
    kabag_verified',
    'asistant_verified',
    'sekda_verified'];

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
    
    public function document_supports(){
        return $this->hasMany(DocumentSupport::class, 'document_id', 'id');
    }
    
}
