<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPayment extends Model
{
    use SoftDeletes;

    public function product_result()
    {
        return $this->hasOne(ProductResult::class, 'product_payment_id', 'id');
    }
}
