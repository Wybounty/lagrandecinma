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
        Schema::create('cinema_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('movie_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('room_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->decimal('price', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cinema_sessions');
    }
};
