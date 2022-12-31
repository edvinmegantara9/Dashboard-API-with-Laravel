<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductResult extends Model
{
    public function payment()
    {
        return $this->belongsTo(ProductPayment::class, 'product_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }
}
