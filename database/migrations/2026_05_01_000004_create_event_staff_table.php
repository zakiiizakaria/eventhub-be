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
        Schema::create('event_staff', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('staff_id');
            $table->boolean('is_attending')->default(false);
            $table->unsignedTinyInteger('pax')->default(1);
            $table->string('table_number')->nullable();
            $table->string('lucky_draw_number')->nullable();
            $table->uuid('invitation_token')->unique();
            $table->timestamps();

            $table->foreign('event_id')
                  ->references('id')
                  ->on('events')
                  ->cascadeOnDelete();

            $table->foreign('staff_id')
                  ->references('id')
                  ->on('staffs')
                  ->cascadeOnDelete();

            $table->unique(['event_id', 'staff_id']);
            $table->unique(['event_id', 'lucky_draw_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_staff');
    }
};
