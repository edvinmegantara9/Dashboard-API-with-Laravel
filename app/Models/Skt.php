<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skt extends Model
{
    protected $fillable = [
        'nama',
        'alamat',
        'id_sub_bagian',
        'deskripsi',
        'id_kualifikasi_profesi',
        'asosiasi',
        'tgl_cetak_sertifikat',
        'provinsi_domisili',
        'kabupaten',
        'provinsi_registrasi'
    ];
}
