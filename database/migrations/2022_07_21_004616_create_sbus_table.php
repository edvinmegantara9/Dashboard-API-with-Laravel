<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSbusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sbus', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pjbu');
            $table->string('nama_badan_usaha');
            $table->string('alamat');
            $table->string('kecamatan');
            $table->string('bentuk');
            $table->string('asosiasi');
            $table->string('sub_klasifikasi_kbli');
            $table->string('kualifikasi_kbli');
            $table->date('tanggal_terbit');
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
        Schema::dropIfExists('sbus');
    }
}
