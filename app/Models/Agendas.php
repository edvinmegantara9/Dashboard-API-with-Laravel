<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agendas extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function schedules()
    {
        return $this->hasMany(AgendaDetails::class, 'agenda_id', 'id');
    }
}
