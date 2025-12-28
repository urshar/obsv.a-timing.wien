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
            $table->unsignedBigInteger('meet_id');
            $table->string('lenex_code');
            $table->unsignedBigInteger('meet_age_group_id');

            $table->unique(['meet_id', 'lenex_code']);
            $table->foreign('meet_age_group_id')->references('id')->on('meet_age_groups')->onDelete('cascade');
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
