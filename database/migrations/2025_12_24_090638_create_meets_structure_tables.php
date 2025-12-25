<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meets', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->foreignId('facility_id')->nullable()->constrained('facilities')->cascadeOnUpdate()->nullOnDelete();

            // course: SCM/LCM/SCY (je nach Lenex)
            $table->string('course', 10)->nullable();

            $table->date('age_date')->nullable();

            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            $table->json('fees_json')->nullable();
            $table->json('qualify_json')->nullable();

            // für Debug/Trace
            $table->string('source_filename')->nullable();
            $table->string('source_hash', 64)->nullable()->index();

            $table->timestamps();

            $table->index(['name', 'start_date']);
            $table->index(['facility_id']);
        });

        Schema::create('meet_age_groups', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('meet_id')->constrained('meets')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('code')->nullable();
            $table->unsignedSmallInteger('min_age')->nullable();
            $table->unsignedSmallInteger('max_age')->nullable();
            $table->char('gender', 1)->nullable();

            $table->string('name')->nullable();

            $table->timestamps();

            $table->index(['meet_id', 'code']);
        });

        Schema::create('meet_sessions', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('meet_id')->constrained('meets')->cascadeOnUpdate()->cascadeOnDelete();

            $table->unsignedSmallInteger('session_no')->nullable();
            $table->string('name')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();

            $table->timestamps();

            $table->index(['meet_id', 'session_no']);
            $table->index(['meet_id', 'date']);
        });

        Schema::create('meet_events', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('meet_session_id')->constrained('meet_sessions')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('meet_age_group_id')->nullable()->constrained('meet_age_groups')->cascadeOnUpdate()->nullOnDelete();

            $table->unsignedSmallInteger('event_no')->nullable();
            $table->string('name')->nullable();

            $table->char('gender', 1)->nullable(); // M/F/X
            $table->unsignedSmallInteger('distance')->nullable();
            $table->string('stroke', 20)->nullable(); // FR/FL/BR/BA/IM etc.
            $table->string('round', 20)->nullable();  // heats/final/...
            $table->boolean('is_relay')->default(false);

            $table->timestamps();

            $table->index(['meet_session_id', 'event_no']);
            $table->index(['distance', 'stroke', 'gender']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meet_events');
        Schema::dropIfExists('meet_sessions');
        Schema::dropIfExists('meet_age_groups');
        Schema::dropIfExists('meets');
    }
};
