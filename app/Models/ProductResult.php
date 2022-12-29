<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductResult extends Model
{
    public function payment()
    {
        return $this->belongsTo(ProductPayment::class, 'product_id', 'id');
    }
}
