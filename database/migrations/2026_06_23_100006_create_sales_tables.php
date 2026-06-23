<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->date('quotation_date');
            $table->date('valid_until')->nullable();
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('quotation_id')->nullable()->constrained()->nullOnDelete();
            $table->date('invoice_date');
            $table->string('invoice_type', 20)->default('cash');
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no', 30)->unique();
            $table->foreignId('sales_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->date('return_date');
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
