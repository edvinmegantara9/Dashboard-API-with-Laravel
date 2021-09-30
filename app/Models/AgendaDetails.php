<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgendaDetails extends Model
{
    use SoftDeletes;

    public function agenda()
    {
        return $this->belongsTo(Agendas::class, 'agenda_id', 'id');
    }
}
