<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->unique();
            $table->string('oem_no')->nullable()->index();
            $table->string('manufacturer_part_no')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->foreignId('brand_id')->constrained();
            $table->foreignId('origin_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('franchise_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('default_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('description_en');
            $table->string('description_ar')->nullable();
            $table->string('type_code')->nullable();
            $table->string('group_code')->nullable();
            $table->string('subgroup_code')->nullable();
            $table->string('gn', 1)->default('G');
            $table->boolean('is_kit')->default(false);
            $table->boolean('is_returnable')->default(false);
            $table->decimal('list_price', 14, 4)->default(0);
            $table->decimal('price_2', 14, 4)->default(0);
            $table->decimal('price_3', 14, 4)->default(0);
            $table->decimal('cost_price', 14, 4)->default(0);
            $table->decimal('sale_pack_qty', 12, 2)->default(1);
            $table->decimal('purchase_pack_qty', 12, 2)->default(1);
            $table->string('hs_code')->nullable();
            $table->decimal('weight', 12, 3)->default(0);
            $table->string('vat_code')->nullable();
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->decimal('max_stock', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
