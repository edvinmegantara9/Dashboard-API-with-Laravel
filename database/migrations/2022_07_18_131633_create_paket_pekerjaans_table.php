<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaketPekerjaansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paket_pekerjaans', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('opd_id');
            $table->string('nama_paket');
            $table->string('jenis_pekerjaan');
            $table->string('sumber_dana');
            $table->integer('nilai_kontrak');
            $table->text('alamat_pekerjaan');
            $table->string('kecamatan');
            $table->string('status_pekerjaan');
            $table->integer('tahun_anggaran');
            $table->string('longitude');
            $table->string('latitude');
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
        Schema::dropIfExists('paket_pekerjaans');
    }
}
