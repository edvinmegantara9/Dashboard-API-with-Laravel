<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentNoticesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents');
            $table->text('description');
            $table->integer('margin_top');
            $table->integer('margin_bottom');
            $table->integer('margin_left');
            $table->integer('margin_right');
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
        Schema::dropIfExists('document_notices');
    }
}
