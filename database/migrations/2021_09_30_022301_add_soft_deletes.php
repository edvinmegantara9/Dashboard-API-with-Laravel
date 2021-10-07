<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('roles_opds', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('message_receivers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('message_attachments', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('chats_receivers', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('agenda_details', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('roles_opds', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('message_receivers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('message_attachments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('document_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('chats', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('chats_receivers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('agendas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('agenda_details', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
