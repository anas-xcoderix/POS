<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legacy_import_maps', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->string('legacy_key', 120);
            $table->unsignedBigInteger('local_id');
            $table->string('legacy_table', 100)->nullable();
            $table->timestamps();
            $table->unique(['entity_type', 'legacy_key']);
            $table->index(['entity_type', 'local_id']);
        });

        Schema::create('legacy_import_runs', function (Blueprint $table) {
            $table->id();
            $table->string('connection', 50);
            $table->string('phase', 50);
            $table->string('status', 20)->default('running');
            $table->unsignedInteger('rows_processed')->default(0);
            $table->unsignedInteger('rows_imported')->default(0);
            $table->unsignedInteger('rows_skipped')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_import_runs');
        Schema::dropIfExists('legacy_import_maps');
    }
};
