<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Donasi extends Model
{
    protected $guarded = [];

    public function restorant()
    {
        return $this->belongsTo(Restorant::class, 'restorant_id', 'id');
    }
}
