<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('athletes', function (Blueprint $table) {
            $table->charset('utf8mb4');
            $table->collation('utf8mb4_unicode_ci');

            $table->id();

            $table->string('first_name');
            $table->string('last_name');

            // Lenex: meist M/F/X – wir speichern als char(1)
            $table->char('gender', 1)->nullable();

            // Pflicht: Jahr
            $table->unsignedSmallInteger('birth_year');

            // optional (oft 01.01.xxxx)
            $table->date('birthdate')->nullable();

            // später Sync mit Splash TeamManager
            $table->string('external_splash_id')->nullable()->index();

            $table->timestamps();

            $table->index(['last_name', 'first_name']);
            $table->index(['birth_year', 'last_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athletes');
    }
};
