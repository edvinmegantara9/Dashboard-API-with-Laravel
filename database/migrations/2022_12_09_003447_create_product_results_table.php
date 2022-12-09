<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('product_payment_id')->constrained('product_payments');
            $table->string('full_name');
            $table->string('nik');
            $table->integer('age');
            $table->string('work');
            $table->string('address');
            $table->string('sim_type');
            $table->string('needs');
            $table->integer('total_point');
            $table->string('status');
            $table->integer('repetition');
            $table->dateTime('expired_at');
            $table->softDeletes();
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
        Schema::dropIfExists('product_results');
    }
}
