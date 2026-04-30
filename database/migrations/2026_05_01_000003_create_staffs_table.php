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
        Schema::create('staffs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organizer_id');
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->foreign('organizer_id')
                  ->references('id')
                  ->on('organizers')
                  ->cascadeOnDelete();

            $table->unique(['organizer_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staffs');
    }
};
