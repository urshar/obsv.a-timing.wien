<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            // Du hast nations/regions bereits – wir hängen hier an nations an.
            $table->foreignId('nation_id')->constrained('nations')->cascadeOnUpdate()->restrictOnDelete();

            $table->string('name');
            $table->string('short_name')->nullable();

            // Global: Clubs die nur Officials haben sollen nicht eingelesen werden.
            $table->boolean('officials_only')->default(false);

            $table->timestamps();

            $table->index(['nation_id', 'name']);
            $table->index(['nation_id', 'short_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
