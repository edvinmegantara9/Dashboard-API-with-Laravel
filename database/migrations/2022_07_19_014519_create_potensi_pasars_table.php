<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePotensiPasarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('potensi_pasars', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('opd_id');
            $table->integer('tahun_anggaran');
            $table->string('sumber_dana');
            $table->string('nilai_pekerjaan');
            $table->string('jenis_pekerjaan');
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
        Schema::dropIfExists('potensi_pasars');
    }
}
