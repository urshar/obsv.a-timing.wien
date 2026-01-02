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
        Schema::create('lenex_age_group_maps', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('meet_id')->constrained('meets')->cascadeOnDelete();
            $table->foreignId('meet_age_group_id')->constrained('meet_age_groups')->cascadeOnDelete();

            $table->unsignedInteger('lenex_code');

            $table->timestamps();

            $table->unique(['meet_id', 'lenex_code']);
            $table->index(['meet_id', 'meet_age_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lenex_age_group_maps');
    }
};
