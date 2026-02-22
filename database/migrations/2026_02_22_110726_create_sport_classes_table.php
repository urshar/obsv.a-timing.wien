<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_classes', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            // z.B. "S10", "SB9", "SM7"
            $table->string('code', 32)->unique();

            // optional: "S", "SB", "SM" oder sonstige Systeme später
            $table->string('discipline', 16)->nullable();

            $table->string('label', 128)->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sport_classes');
    }
};
