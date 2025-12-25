<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->string('name');
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('address')->nullable();
            $table->string('country_code', 3)->nullable(); // AUT etc.

            $table->timestamps();

            $table->index(['name', 'city']);
            $table->index(['city']);
        });

        Schema::create('facility_pools', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->unsignedSmallInteger('length_m')->nullable(); // 25/50
            $table->unsignedTinyInteger('lanes')->nullable();
            $table->boolean('indoor')->nullable();

            $table->timestamps();

            $table->index(['facility_id', 'length_m']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_pools');
        Schema::dropIfExists('facilities');
    }
};
