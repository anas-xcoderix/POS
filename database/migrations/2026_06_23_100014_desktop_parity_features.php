<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('locations', 'location_type')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->string('location_type', 20)->default('warehouse')->after('name');
            });
        }

        if (! Schema::hasColumn('parts', 'track_batch')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->boolean('track_batch')->default(false)->after('is_active');
                $table->boolean('track_serial')->default(false)->after('track_batch');
            });
        }

        if (! Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 3)->unique();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->string('symbol', 10)->default('');
                $table->decimal('exchange_rate', 14, 6)->default(1);
                $table->boolean('is_base')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('currency_rates')) {
            Schema::create('currency_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
                $table->date('rate_date');
                $table->decimal('exchange_rate', 14, 6);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['currency_id', 'rate_date']);
            });
        }

        if (! Schema::hasColumn('sales_invoices', 'currency_id')) {
            foreach (['sales_invoices', 'purchase_invoices', 'quotations'] as $tableName) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('currency_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
                    $table->decimal('exchange_rate', 14, 6)->default(1)->after('currency_id');
                    $table->decimal('foreign_total', 14, 2)->nullable()->after('total_amount');
                });
            }
        }

        if (! Schema::hasColumn('payment_receipts', 'currency_id')) {
            Schema::table('payment_receipts', function (Blueprint $table) {
                $table->foreignId('currency_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
                $table->decimal('exchange_rate', 14, 6)->default(1)->after('currency_id');
                $table->decimal('foreign_amount', 14, 2)->nullable()->after('amount');
            });
        }

        if (! Schema::hasTable('stock_batches')) {
            Schema::create('stock_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained();
                $table->foreignId('location_id')->constrained();
                $table->foreignId('part_id')->constrained();
                $table->string('batch_no', 50);
                $table->string('lot_no', 50)->nullable();
                $table->string('serial_no', 100)->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('quantity', 14, 2)->default(0);
                $table->decimal('unit_cost', 14, 4)->default(0);
                $table->date('received_date');
                $table->string('reference_type', 50)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_no', 50)->nullable();
                $table->timestamps();
                $table->index(['part_id', 'branch_id', 'location_id']);
            });
        }

        if (! Schema::hasColumn('stock_movements', 'stock_batch_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('stock_batch_id')->nullable()->after('part_id')->constrained()->nullOnDelete();
            });
        }

        if (! Schema::hasTable('fixed_asset_categories')) {
            Schema::create('fixed_asset_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->unsignedSmallInteger('default_life_months')->default(60);
                $table->foreignId('asset_account_id')->nullable()->constrained('accounts')->nullOnDelete();
                $table->foreignId('depreciation_account_id')->nullable()->constrained('accounts')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('fixed_assets')) {
            Schema::create('fixed_assets', function (Blueprint $table) {
                $table->id();
                $table->string('asset_code', 30)->unique();
                $table->foreignId('category_id')->constrained('fixed_asset_categories');
                $table->foreignId('branch_id')->constrained();
                $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('name_ar')->nullable();
                $table->date('purchase_date');
                $table->decimal('purchase_value', 14, 2);
                $table->decimal('salvage_value', 14, 2)->default(0);
                $table->unsignedSmallInteger('useful_life_months');
                $table->string('depreciation_method', 20)->default('straight_line');
                $table->decimal('accumulated_depreciation', 14, 2)->default(0);
                $table->decimal('net_book_value', 14, 2)->default(0);
                $table->string('status', 20)->default('active');
                $table->date('disposed_at')->nullable();
                $table->text('remarks')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('fixed_asset_depreciations')) {
            Schema::create('fixed_asset_depreciations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
                $table->unsignedSmallInteger('dep_year');
                $table->unsignedTinyInteger('dep_month');
                $table->decimal('amount', 14, 2);
                $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('posted_at')->nullable();
                $table->timestamps();
                $table->unique(['fixed_asset_id', 'dep_year', 'dep_month'], 'fa_dep_unique');
            });
        }

        if (! Schema::hasTable('proforma_invoices')) {
            Schema::create('proforma_invoices', function (Blueprint $table) {
                $table->id();
                $table->string('proforma_no', 30)->unique();
                $table->foreignId('branch_id')->constrained();
                $table->foreignId('customer_id')->constrained();
                $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('exchange_rate', 14, 6)->default(1);
                $table->date('proforma_date');
                $table->date('valid_until')->nullable();
                $table->string('status', 20)->default('draft');
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('discount_amount', 14, 2)->default(0);
                $table->decimal('vat_amount', 14, 2)->default(0);
                $table->decimal('total_amount', 14, 2)->default(0);
                $table->decimal('foreign_total', 14, 2)->nullable();
                $table->foreignId('sales_invoice_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('proforma_invoice_items')) {
            Schema::create('proforma_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('proforma_invoice_id')->constrained()->cascadeOnDelete();
                $table->foreignId('part_id')->constrained();
                $table->decimal('quantity', 14, 2);
                $table->decimal('unit_price', 14, 4);
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->decimal('vat_percent', 5, 2)->default(0);
                $table->decimal('line_total', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pos_terminals')) {
            Schema::create('pos_terminals', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name');
                $table->foreignId('branch_id')->constrained();
                $table->foreignId('default_location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pos_sessions')) {
            Schema::create('pos_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_no', 30)->unique();
                $table->foreignId('pos_terminal_id')->constrained();
                $table->foreignId('branch_id')->constrained();
                $table->foreignId('user_id')->constrained();
                $table->timestamp('opened_at');
                $table->timestamp('closed_at')->nullable();
                $table->decimal('opening_float', 14, 2)->default(0);
                $table->decimal('closing_float', 14, 2)->nullable();
                $table->decimal('total_sales', 14, 2)->default(0);
                $table->string('status', 20)->default('open');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('sales_invoices', 'source')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->foreignId('proforma_invoice_id')->nullable()->after('quotation_id')->constrained()->nullOnDelete();
                $table->foreignId('pos_session_id')->nullable()->after('proforma_invoice_id')->constrained()->nullOnDelete();
                $table->string('source', 20)->default('standard')->after('invoice_type');
            });
        }

        if (! Schema::hasTable('pick_tickets')) {
            Schema::create('pick_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('pick_no', 30)->unique();
                $table->foreignId('sales_invoice_id')->constrained();
                $table->foreignId('branch_id')->constrained();
                $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
                $table->string('status', 20)->default('open');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('picked_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pick_ticket_items')) {
            Schema::create('pick_ticket_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pick_ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sales_invoice_item_id')->constrained();
                $table->foreignId('part_id')->constrained();
                $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('stock_batch_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('qty_ordered', 14, 2);
                $table->decimal('qty_picked', 14, 2)->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('cash_book_entries')) {
            Schema::create('cash_book_entries', function (Blueprint $table) {
                $table->id();
                $table->string('entry_no', 30)->unique();
                $table->foreignId('branch_id')->constrained();
                $table->date('entry_date');
                $table->string('entry_type', 20);
                $table->foreignId('account_id')->constrained();
                $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('exchange_rate', 14, 6)->default(1);
                $table->decimal('amount', 14, 2);
                $table->decimal('foreign_amount', 14, 2)->nullable();
                $table->string('party_type', 30)->nullable();
                $table->unsignedBigInteger('party_id')->nullable();
                $table->string('reference_no', 50)->nullable();
                $table->string('reference_type', 50)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->text('description')->nullable();
                $table->decimal('running_balance', 14, 2)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['branch_id', 'entry_date']);
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action', 50);
                $table->string('auditable_type', 100)->nullable();
                $table->unsignedBigInteger('auditable_id')->nullable();
                $table->string('document_no', 50)->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('remarks')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['auditable_type', 'auditable_id']);
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('permission', 80);
                $table->boolean('granted')->default(true);
                $table->timestamps();
                $table->unique(['user_id', 'permission']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('cash_book_entries');
        Schema::dropIfExists('pick_ticket_items');
        Schema::dropIfExists('pick_tickets');

        if (Schema::hasColumn('sales_invoices', 'source')) {
            Schema::table('sales_invoices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('pos_session_id');
                $table->dropConstrainedForeignId('proforma_invoice_id');
                $table->dropColumn('source');
            });
        }

        Schema::dropIfExists('pos_sessions');
        Schema::dropIfExists('pos_terminals');
        Schema::dropIfExists('proforma_invoice_items');
        Schema::dropIfExists('proforma_invoices');
        Schema::dropIfExists('fixed_asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('fixed_asset_categories');

        if (Schema::hasColumn('stock_movements', 'stock_batch_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('stock_batch_id');
            });
        }

        Schema::dropIfExists('stock_batches');

        foreach (['sales_invoices', 'purchase_invoices', 'quotations'] as $tableName) {
            if (Schema::hasColumn($tableName, 'currency_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('currency_id');
                    $table->dropColumn(['exchange_rate', 'foreign_total']);
                });
            }
        }

        if (Schema::hasColumn('payment_receipts', 'currency_id')) {
            Schema::table('payment_receipts', function (Blueprint $table) {
                $table->dropConstrainedForeignId('currency_id');
                $table->dropColumn(['exchange_rate', 'foreign_amount']);
            });
        }

        Schema::dropIfExists('currency_rates');
        Schema::dropIfExists('currencies');

        if (Schema::hasColumn('parts', 'track_batch')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->dropColumn(['track_batch', 'track_serial']);
            });
        }

        if (Schema::hasColumn('locations', 'location_type')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropColumn('location_type');
            });
        }
    }
};
