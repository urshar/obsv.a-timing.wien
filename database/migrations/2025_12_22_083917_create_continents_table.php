<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('continents', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->string('code', 10)->unique();     // z.B. EU, AF, AS, NA, SA, OC, AN
            $table->string('nameEn', 200);
            $table->string('nameDe', 200)->nullable();

            $table->timestamps();

            $table->index('nameEn');
            $table->index('nameDe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('continents');
    }
};
