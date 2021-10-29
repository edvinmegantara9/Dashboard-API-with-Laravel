<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
        });

        Schema::table('roles_opds', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('opd_id')->constrained('roles')->onDelete('cascade');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('upload_by')->constrained('roles')->onDelete('cascade');
            $table->foreignId('document_type')->constrained('document_types')->onDelete('cascade');
        });

        Schema::table('public_documents', function (Blueprint $table) {
            $table->foreignId('sub_document_type')->constrained('document_types')->onDelete('cascade');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('created_by')->constrained('roles')->onDelete('cascade');
        });

        Schema::table('room_receivers', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('roles')->onDelete('cascade');
        });

        Schema::table('message_receivers', function (Blueprint $table) {
            $table->foreignId('receiver_id')->constrained('roles')->onDelete('cascade');
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
        });

        Schema::table('message_attachments', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
        });

        Schema::table('agenda_details', function(Blueprint $table) {
            $table->foreignId('agenda_id')->constrained('agendas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
