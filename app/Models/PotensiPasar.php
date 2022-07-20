<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PotensiPasar extends Model
{
    protected $fillable = [
        'user_id',
        'opd_id',
        'sumber_dana',
        'tahun_anggaran',
        'nilai_pekerjaan',
        'jenis_pekerjaan'
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Roles::class, 'opd_id', 'id');
    }
}
