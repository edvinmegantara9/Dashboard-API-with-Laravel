<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('public_documents', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('title');
        //     $table->string('name');
        //     $table->smallInteger('document_type');
        //     // 0 = Dokumen Kota
        //     // 1 = PP
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('public_documents');
    }
}
