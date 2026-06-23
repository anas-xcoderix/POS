<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('license_no', 50)->nullable();
            $table->string('vehicle_plate', 20)->nullable();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('transport_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('transport_driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_note_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sales_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->date('ship_date');
            $table->date('expected_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('ship_to_address', 500)->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->decimal('transport_charge', 14, 2)->default(0);
            $table->decimal('cod_amount', 14, 2)->default(0);
            $table->decimal('cod_collected', 14, 2)->default(0);
            $table->boolean('cod_settled')->default(false);
            $table->string('vehicle_plate', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'ship_date']);
            $table->index(['transport_driver_id', 'ship_date']);
        });

        Schema::create('transport_cash_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('transport_driver_id')->constrained();
            $table->date('voucher_date');
            $table->decimal('total_amount', 14, 2);
            $table->string('status', 20)->default('draft');
            $table->foreignId('cash_book_entry_id')->nullable()->constrained('cash_book_entries')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_cash_voucher_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_cash_voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_shipment_id')->constrained();
            $table->decimal('amount', 14, 2);
            $table->timestamps();
            $table->unique(['transport_cash_voucher_id', 'transport_shipment_id'], 'transport_voucher_shipment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_cash_voucher_items');
        Schema::dropIfExists('transport_cash_vouchers');
        Schema::dropIfExists('transport_shipments');
        Schema::dropIfExists('transport_drivers');
    }
};
