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

            $table->unsignedBigInteger('meet_event_id');
            $table->unsignedBigInteger('age_group_id');

            $table->primary(['meet_event_id', 'age_group_id']);

            $table->foreign('meet_event_id')
                ->references('id')->on('meet_events')
                ->onDelete('cascade');

            $table->foreign('age_group_id')
                ->references('id')->on('meet_age_groups')
                ->onDelete('cascade');

            $table->index('age_group_id');
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
