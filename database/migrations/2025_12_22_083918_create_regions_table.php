<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->foreignId('nation_id')
                ->constrained('nations')
                ->cascadeOnDelete();

            $table->string('nameEn', 250);
            $table->string('nameDe', 250)->nullable();

            $table->string('lsvCode', 50)->nullable();
            $table->string('bsvCode', 50)->nullable();
            $table->string('isoSubRegionCode', 50)->nullable(); // falls vorhanden (z.B. AT-1, AT-9 etc.)
            $table->string('abbreviation', 20)->nullable();     // z.B. BL

            $table->timestamps();

            $table->index(['nation_id', 'nameEn']);
            $table->index('nameDe');

            // Sinnvolle Eindeutigkeit (optional, aber meist gewünscht):
            $table->unique(['nation_id', 'isoSubRegionCode'], 'regions_nation_isoSubRegionCode_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
