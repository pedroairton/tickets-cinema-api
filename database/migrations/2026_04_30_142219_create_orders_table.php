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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('total_amount', 10, 2);

            $table->enum('payment_method', ['credit_card', 'debit_card', 'pix'])->nullable();

            $table->enum('payment_status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('payment_status');
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
