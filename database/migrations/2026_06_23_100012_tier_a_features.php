<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no', 30)->unique();
            $table->foreignId('purchase_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->date('return_date');
            $table->string('status', 20)->default('draft');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('vat_percent', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 30)->unique();
            $table->string('party_type', 20);
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained();
            $table->date('receipt_date');
            $table->string('payment_method', 30)->default('cash');
            $table->decimal('amount', 14, 2);
            $table->string('reference_no')->nullable();
            $table->foreignId('sales_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['year', 'month']);
        });

        Schema::create('stock_count_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('count_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->date('count_date');
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->decimal('system_qty', 14, 2)->default(0);
            $table->decimal('counted_qty', 14, 2)->default(0);
            $table->decimal('variance', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('part_kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_part_id')->constrained('parts')->cascadeOnDelete();
            $table->foreignId('component_part_id')->constrained('parts')->cascadeOnDelete();
            $table->decimal('quantity', 14, 2)->default(1);
            $table->timestamps();
            $table->unique(['kit_part_id', 'component_part_id']);
        });

        Schema::create('part_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alternative_part_id')->constrained('parts')->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['part_id', 'alternative_part_id']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('remarks');
            $table->string('void_reason')->nullable()->after('voided_at');
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('remarks');
            $table->string('void_reason')->nullable()->after('voided_at');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->string('entry_type', 20)->default('auto')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', fn (Blueprint $t) => $t->dropColumn('entry_type'));
        Schema::table('purchase_invoices', fn (Blueprint $t) => $t->dropColumn(['voided_at', 'void_reason']));
        Schema::table('sales_invoices', fn (Blueprint $t) => $t->dropColumn(['voided_at', 'void_reason']));
        Schema::dropIfExists('part_alternatives');
        Schema::dropIfExists('part_kits');
        Schema::dropIfExists('stock_count_items');
        Schema::dropIfExists('stock_count_sessions');
        Schema::dropIfExists('fiscal_periods');
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};
