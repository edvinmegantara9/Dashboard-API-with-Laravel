<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductDetails extends Model
{
    use SoftDeletes;

    public function multiple_choices()
    {
        return $this->hasMany(MultipleChoice::class, 'product_detail_id', 'id');
    }
}
