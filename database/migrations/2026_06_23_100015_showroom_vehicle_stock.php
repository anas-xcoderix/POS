<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showroom_vehicle_models', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->foreignId('franchise_id')->nullable()->constrained()->nullOnDelete();
            $table->string('make', 80)->nullable();
            $table->unsignedSmallInteger('model_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('showroom_colors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('showroom_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('stock_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('model_id')->constrained('showroom_vehicle_models');
            $table->foreignId('color_id')->nullable()->constrained('showroom_colors')->nullOnDelete();
            $table->foreignId('franchise_id')->nullable()->constrained()->nullOnDelete();
            $table->string('chassis_no', 50)->unique();
            $table->string('engine_no', 50)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->decimal('purchase_cost', 14, 2)->default(0);
            $table->decimal('list_price', 14, 2)->default(0);
            $table->string('status', 20)->default('in_stock');
            $table->date('received_date');
            $table->date('sold_date')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sales_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['branch_id', 'status']);
        });

        Schema::create('showroom_vehicle_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 30)->unique();
            $table->foreignId('showroom_vehicle_id')->constrained();
            $table->foreignId('from_branch_id')->constrained('branches');
            $table->foreignId('to_branch_id')->constrained('branches');
            $table->date('transfer_date');
            $table->string('status', 20)->default('pending');
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showroom_vehicle_transfers');
        Schema::dropIfExists('showroom_vehicles');
        Schema::dropIfExists('showroom_colors');
        Schema::dropIfExists('showroom_vehicle_models');
    }
};
