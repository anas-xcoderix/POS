<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('origins', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->string('code', 30);
            $table->string('name');
            $table->string('aisle')->nullable();
            $table->string('rack')->nullable();
            $table->string('bin')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['branch_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
        Schema::dropIfExists('units');
        Schema::dropIfExists('franchises');
        Schema::dropIfExists('origins');
        Schema::dropIfExists('brands');
    }
};
