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
        Schema::create('age_group_event', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->foreignId('meet_event_id')->constrained('meet_events')->cascadeOnDelete();
            $table->foreignId('age_group_id')->constrained('meet_age_groups')->cascadeOnDelete();

            $table->unique(['meet_event_id', 'age_group_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_group_event');
    }
};
