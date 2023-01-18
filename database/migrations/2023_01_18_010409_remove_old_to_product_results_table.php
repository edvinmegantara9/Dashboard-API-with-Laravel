<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOldToProductResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_results', function (Blueprint $table) {
            $table->dropColumn('age');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->text('term_and_condition')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_results', function (Blueprint $table) {
            //
        });
    }
}
