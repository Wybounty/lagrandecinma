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
        Schema::create('movies', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description');
            $table->string('slug')->unique();

            $table->string('genre');
            $table->unsignedSmallInteger('duration'); // en minutes

            $table->date('release_date');

            $table->string('poster'); // chemin de l'affiche
            $table->string('trailer_url')->nullable(); // lien YouTube par exemple

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
