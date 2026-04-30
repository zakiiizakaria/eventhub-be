<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds the foreign key from admins.organizer_id → organizers.id
     * after both tables exist. It runs after organizers is created (000001).
     */
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->foreign('organizer_id')
                  ->references('id')
                  ->on('organizers')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['organizer_id']);
        });
    }
};
