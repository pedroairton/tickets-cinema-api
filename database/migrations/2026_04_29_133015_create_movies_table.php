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
            $table->string('slug')->unique();
            $table->string('synopsis');
            $table->unsignedSmallInteger('duration_minutes');
            $table->string('image_url')->nullable();
            $table->string('trailer_url')->nullable();
            
            $table->enum('age_rating', ['L', '10', '12', '14', '16', '18'])
            ->default('L')
            ->comment('L: Livre, 10, 12, 14, 16, 18 anos');

            $table->string('original_title')->nullable();
            $table->string('director')->nullable();
            $table->string('distributor')->nullable();
            $table->string('country_of_origin', 100)->nullable();

            $table->enum('status', ['showing', 'coming_soon', 'off_screen'])
            ->default('coming_soon')
            ->comment('showing=Em cartaz, coming_soon=Em breve, off_screen=Fora de cartaz');

            $table->date('release_date');

            $table->timestamps();

            $table->index('status');
            $table->index('age_rating');
            $table->index('release_date');
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
