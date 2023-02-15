<?php

use App\Models\Document;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVerifiedToDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('legal_drafter')->nullable()->constrained('users');
            $table->foreignId('admin_verified')->nullable()->constrained('users');
            $table->dateTime('admin_verified_at')->nullable();
            $table->foreignId('legal_drafter_verified')->nullable()->constrained('users');
            $table->dateTime('legal_drafter_verified_at')->nullable();
            $table->foreignId('suncang_verified')->nullable()->constrained('users');
            $table->dateTime('suncang_verified_at')->nullable();
            $table->foreignId('kasubag_verified')->nullable()->constrained('users');
            $table->dateTime('kasubag_verified_at')->nullable();
            $table->text('kasubag_verified_sign')->nullable();
            $table->foreignId('kabag_verified')->nullable()->constrained('users');
            $table->dateTime('kabag_verified_at')->nullable();
            $table->text('kabag_verified_sign')->nullable();;
            $table->foreignId('asistant_verified')->nullable()->constrained('users');
            $table->dateTime('asistant_verified_at')->nullable();
            $table->text('asistant_verified_sign')->nullable();;
            $table->foreignId('sekda_verified')->nullable()->constrained('users');
            $table->dateTime('sekda_verified_at')->nullable();
            $table->text('sekda_verified_sign')->nullable();;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            //
        });
    }
}
