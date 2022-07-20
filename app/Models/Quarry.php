<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quarry extends Model
{
    public function document_quarries()
    {
        return $this->hasMany(DocumentQuarry::class, 'quarry_id', 'id');
    }
}
