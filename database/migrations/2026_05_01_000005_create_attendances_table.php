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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('staff_id');
            $table->timestamp('scanned_at')->useCurrent();
            $table->uuid('scanned_by');
            $table->timestamps();

            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->cascadeOnDelete();

            $table->foreign('staff_id')
                  ->references('id')
                  ->on('staffs')
                  ->cascadeOnDelete();

            $table->foreign('scanned_by')
                  ->references('id')
                  ->on('admins')
                  ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
