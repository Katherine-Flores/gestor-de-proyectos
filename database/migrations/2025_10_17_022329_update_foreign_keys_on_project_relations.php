<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla project_user
        Schema::table('project_user', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->onDelete('cascade');
        });

        // Tabla resources
        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->onDelete('cascade');
        });

        // Tabla updates
        Schema::table('updates', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_user', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        Schema::table('updates', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });
    }
};
