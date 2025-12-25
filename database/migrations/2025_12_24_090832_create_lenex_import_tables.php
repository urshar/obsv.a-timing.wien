<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_batches', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            // meet_structure / entries / results / records
            $table->string('type', 30);

            $table->string('filename')->nullable();
            $table->string('file_hash', 64)->nullable()->index();

            $table->string('status', 20)->default('preview'); // preview/committed/failed

            $table->foreignId('meet_id')->nullable()->constrained('meets')->cascadeOnUpdate()->nullOnDelete();

            $table->json('summary_json')->nullable();

            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('import_issues', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnUpdate()->cascadeOnDelete();

            // club/athlete/facility/meet/...
            $table->string('entity_type', 30);
            // eindeutiger key aus Quelle (z.B. "club:AUT|SC Diana Wien")
            $table->string('entity_key', 255);

            // ok/warn/error
            $table->string('severity', 10)->default('warn');

            $table->string('message');
            $table->json('payload_json')->nullable();

            // Vorschläge fürs Matching (IDs + Labels)
            $table->json('suggestions_json')->nullable();

            $table->timestamps();

            $table->index(['import_batch_id', 'entity_type']);
            $table->index(['entity_type', 'entity_key']);
            $table->index(['severity']);
        });

        Schema::create('import_mappings', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();
            $table->foreignId('import_batch_id')->constrained('import_batches')->cascadeOnUpdate()->cascadeOnDelete();

            $table->string('entity_type', 30);
            $table->string('source_key', 255);

            // create/link/ignore
            $table->string('action', 20)->default('create');

            // Ziel-ID (z.B. clubs.id oder athletes.id)
            $table->unsignedBigInteger('target_id')->nullable();

            $table->timestamps();

            $table->unique(['import_batch_id', 'entity_type', 'source_key']);
            $table->index(['entity_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_mappings');
        Schema::dropIfExists('import_issues');
        Schema::dropIfExists('import_batches');
    }
};
