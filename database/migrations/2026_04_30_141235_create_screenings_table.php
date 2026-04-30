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
        Schema::create('screenings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('movie_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('room_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('price', 5, 2);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('start_time');
            $table->index(['movie_id', 'start_time']);
            $table->index(['room_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screenings');
    }
};
