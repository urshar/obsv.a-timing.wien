<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meet_entries', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->foreignId('meet_event_id')->constrained('meet_events')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('athlete_id')->nullable()->constrained('athletes')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->cascadeOnUpdate()->nullOnDelete();

            // para swim style nur bei entries/results
            $table->foreignId('para_swim_style_id')->nullable()->constrained('para_swim_styles')->cascadeOnUpdate()->nullOnDelete();

            // Seed/entry time
            $table->string('seed_time', 20)->nullable();

            // optional: heat/lane
            $table->unsignedSmallInteger('heat')->nullable();
            $table->unsignedSmallInteger('lane')->nullable();

            $table->timestamps();

            $table->index(['meet_event_id', 'club_id']);
            $table->index(['meet_event_id', 'athlete_id']);
        });

        Schema::create('meet_results', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->foreignId('meet_event_id')->constrained('meet_events')->cascadeOnUpdate()->cascadeOnDelete();

            $table->foreignId('athlete_id')->nullable()->constrained('athletes')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->cascadeOnUpdate()->nullOnDelete();

            $table->foreignId('para_swim_style_id')->nullable()->constrained('para_swim_styles')->cascadeOnUpdate()->nullOnDelete();

            $table->string('result_time', 20)->nullable(); // "1:02.34" o.ä.
            $table->string('status', 10)->nullable();      // OK/DSQ/DNF/...
            $table->unsignedSmallInteger('rank')->nullable();
            $table->unsignedInteger('points')->nullable();

            $table->string('reaction_time', 20)->nullable();

            $table->timestamps();

            $table->index(['meet_event_id', 'rank']);
            $table->index(['meet_event_id', 'club_id']);
            $table->index(['meet_event_id', 'athlete_id']);
        });

        Schema::create('meet_result_splits', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('meet_result_id')->constrained('meet_results')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unsignedSmallInteger('distance')->nullable(); // 50/100/...
            $table->string('split_time', 20)->nullable();

            $table->timestamps();

            $table->index(['meet_result_id', 'distance']);
        });

        // Optional/Später: mehrere Punktesysteme
        Schema::create('result_points', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('meet_result_id')->constrained('meet_results')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('points_table_key', 50); // z.B. "FINA_2025" / "CUSTOM_VIENNA"
            $table->unsignedInteger('points')->nullable();

            $table->timestamps();

            $table->unique(['meet_result_id', 'points_table_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_points');
        Schema::dropIfExists('meet_result_splits');
        Schema::dropIfExists('meet_results');
        Schema::dropIfExists('meet_entries');
    }
};
