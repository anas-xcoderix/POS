<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('group', 50)->default('general');
            $table->timestamps();
        });

        Schema::create('discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('rule_type', 30);
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('customer_type', 30)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->unsignedTinyInteger('price_level')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedTinyInteger('price_level')->default(1)->after('customer_type');
            $table->decimal('discount_percent', 5, 2)->default(0)->after('price_level');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->decimal('max_discount_percent', 5, 2)->default(100)->after('role');
            $table->boolean('can_access_all_branches')->default(false)->after('max_discount_percent');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['max_discount_percent', 'can_access_all_branches']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['price_level', 'discount_percent']);
        });

        Schema::dropIfExists('discount_rules');
        Schema::dropIfExists('system_settings');
    }
};
