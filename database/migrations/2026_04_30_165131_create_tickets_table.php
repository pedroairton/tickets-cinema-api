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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20);

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('screening_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('seat_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('unit_price', 8, 2);

            $table->enum('status', ['active', 'used', 'cancelled'])->default('active');

            $table->timestamps();

            $table->unique(['screening_id', 'seat_id']);

            $table->index(['user_id', 'status']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
