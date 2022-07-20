<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    public function document_labs()
    {
        return $this->hasMany(DocumentLab::class, 'lab_id', 'id');
    }
}
