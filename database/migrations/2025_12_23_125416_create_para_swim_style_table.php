<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('para_swim_styles', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->string('key')->unique();

            $table->unsignedTinyInteger('relay_count')->nullable();
            $table->unsignedSmallInteger('distance');
            $table->string('stroke', 10);

            $table->string('stroke_name_en');
            $table->string('stroke_name_de');

            $table->string('abbreviation', 10);

            $table->timestamps();

            $table->index(['distance', 'stroke', 'relay_count']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('para_swim_styles');
    }
};
