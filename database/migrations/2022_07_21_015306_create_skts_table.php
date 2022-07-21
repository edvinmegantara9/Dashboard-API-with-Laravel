<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSktsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skts', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('alamat');
            $table->string('id_sub_bagian');
            $table->string('deskripsi');
            $table->string('id_kualifikasi_profesi');
            $table->string('asosiasi');
            $table->string('tgl_cetak_sertifikat');
            $table->string('provinsi_domisili');
            $table->string('kabupaten');
            $table->string('provinsi_registrasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('skts');
    }
}
