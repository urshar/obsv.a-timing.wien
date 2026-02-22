<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athlete_sportclass_histories', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->foreignId('athlete_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sport_class_id')->constrained('sport_classes');
            $table->string('discipline', 8)->after('sport_class_id');

            // Gültigkeit (current = valid_to NULL)
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();

            // Herkunft / Nachvollziehbarkeit
            $table->string('source', 32)->default('manual'); // 'lenex', 'manual', 'api'
            $table->string('source_ref', 128)->nullable();   // z.B. Lenex athleteId, Import-Hash
            $table->foreignId('meet_id')->nullable()->constrained()->nullOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['athlete_id', 'discipline', 'valid_from']);
            $table->index(['athlete_id', 'valid_to']);
            $table->index(['sport_class_id']);

            // Optional: hilft gegen exakte Duplikate (nicht gegen "zwei offene Records")
            $table->unique(['athlete_id', 'discipline', 'sport_class_id', 'valid_from', 'source'],
                'ath_sc_from_src_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athlete_sportclass_histories');
    }
};
