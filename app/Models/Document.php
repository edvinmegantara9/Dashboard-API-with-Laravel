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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function adminVerified(){
        return $this->belongsTo(User::class, 'admin_verified', 'id');
    }
    
    public function legalDrafter(){
        return $this->belongsTo(User::class, 'legal_drafter', 'id');
    }

    public function legalDrafterVerified(){
        return $this->belongsTo(User::class, 'legal_drafter_verified', 'id');
    }

    public function suncangVerified(){
        return $this->belongsTo(User::class, 'suncang_verified', 'id');
    }

    public function kasubagVerified(){
        return $this->belongsTo(User::class, 'kasubag_verified', 'id');
    }

    public function kabagVerified(){
        return $this->belongsTo(User::class, 'kabag_verified', 'id');
    }

    public function asistantVerified(){
        return $this->belongsTo(User::class, 'asistant_verified', 'id');
    }

    public function sekdaVerified(){
        return $this->belongsTo(User::class, 'sekda_verified', 'id');
    }

    public function documentConsider(){
        return $this->hasMany(DocumentConsider::class, 'document_id', 'id');
    }

    public function documentDecision(){
        return $this->hasMany(DocumentDecision::class, 'document_id', 'id');
    }

    public function documentRemember(){
        return $this->hasMany(DocumentRemember::class, 'document_id', 'id');
    }

    public function documentNotice(){
        return $this->hasMany(DocumentNotice::class, 'document_id', 'id');
    }

    public function documentStatus(){
        return $this->hasMany(DocumentStatus::class, 'document_id', 'id');
    }

    public function documentAttachment(){
        return $this->hasMany(DocumentAttachment::class, 'document_id', 'id');
    }
    
    public function documentSupport(){
        return $this->hasMany(DocumentSupport::class, 'document_id', 'id');
    }
    
}
