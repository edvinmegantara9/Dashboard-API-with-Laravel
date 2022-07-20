<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlatKontruksi extends Model
{
    public function document_alat_kontruksis()
    {
        return $this->hasMany(DocumentAlatKontruksi::class, 'alat_kontruksi_id', 'id');
    }
}
