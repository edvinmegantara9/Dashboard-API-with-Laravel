<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanningSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planning_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('plan');
            $table->string('schedule');
            $table->smallInteger('type'); // 0 = APBD Induk, 1 = APBD Perubahan
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
        Schema::dropIfExists('planning_schedules');
    }
}
