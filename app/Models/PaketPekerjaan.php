<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketPekerjaan extends Model
{
    protected $fillable = [
        'user_id',
        'opd_id',
        'nama_paket',
        'jenis_pekerjaan',
        'sumber_dana',
        'nilai_kontrak',
        'alamat_pekerjaan',
        'kecamatan',
        'status_pekerjaan',
        'tahun_anggaran',
        'longitude',
        'latitude'
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
