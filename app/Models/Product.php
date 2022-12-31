<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public function product_details()
    {
        return $this->hasMany(ProductDetails::class, 'product_id', 'id');
    }
}
