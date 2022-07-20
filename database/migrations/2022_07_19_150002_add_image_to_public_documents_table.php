<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToPublicDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('public_documents', function (Blueprint $table) {
            $table->string('image')->default('https://stikeskesdamudayana.ac.id/wp-content/uploads/2016/10/default.jpg');
            $table->integer('tahun');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('public_documents', function (Blueprint $table) {
            //
        });
    }
}
