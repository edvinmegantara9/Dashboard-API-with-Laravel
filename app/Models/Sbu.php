<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sbu extends Model
{
    protected $fillable = [
        'nama_pjbu',
        'nama_badan_usaha',
        'alamat',
        'kecamatan',
        'bentuk',
        'asosiasi',
        'sub_klasifikasi_kbli',
        'kualifikasi_kbli',
        'tanggal_terbit',
    ];
}
