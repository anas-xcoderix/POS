<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('plate_no', 20)->unique();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('year', 10)->nullable();
            $table->string('vin')->nullable();
            $table->string('color')->nullable();
            $table->date('istimara_expiry')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();
            $table->string('job_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->date('job_date');
            $table->date('promised_date')->nullable();
            $table->string('status', 20)->default('open');
            $table->decimal('labor_total', 14, 2)->default(0);
            $table->decimal('parts_total', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('mechanic_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('complaint')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('job_card_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_card_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 20)->default('part');
            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity', 14, 2)->default(1);
            $table->decimal('unit_price', 14, 4)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_card_items');
        Schema::dropIfExists('job_cards');
        Schema::dropIfExists('vehicles');
    }
};
