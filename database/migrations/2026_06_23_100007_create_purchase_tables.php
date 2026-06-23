<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->date('po_date');
            $table->date('expected_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('received_qty', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->date('invoice_date');
            $table->string('vendor_invoice_no')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};
