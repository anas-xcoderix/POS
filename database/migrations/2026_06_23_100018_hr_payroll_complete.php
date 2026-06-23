<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('gosi_eligible')->default(false)->after('transport_allowance');
            $table->string('gosi_number', 30)->nullable()->after('gosi_eligible');
            $table->string('bank_name', 100)->nullable()->after('gosi_number');
            $table->string('bank_account', 50)->nullable()->after('bank_name');
            $table->decimal('overtime_rate', 8, 2)->default(0)->after('bank_account');
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('overtime_amount', 12, 2)->default(0)->after('transport_allowance');
            $table->decimal('bonus_amount', 12, 2)->default(0)->after('overtime_amount');
            $table->decimal('gosi_deduction', 12, 2)->default(0)->after('deductions');
            $table->decimal('loan_deduction', 12, 2)->default(0)->after('gosi_deduction');
            $table->decimal('other_deductions', 12, 2)->default(0)->after('loan_deduction');
            $table->string('notes', 255)->nullable()->after('net_pay');
        });

        Schema::table('payroll_runs', function (Blueprint $table) {
            $table->string('payment_status', 20)->default('unpaid')->after('posted_at');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->string('payment_reference', 100)->nullable()->after('paid_at');
        });

        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->date('holiday_date');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['holiday_date', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_holidays');

        Schema::table('payroll_runs', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_at', 'payment_reference']);
        });

        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['overtime_amount', 'bonus_amount', 'gosi_deduction', 'loan_deduction', 'other_deductions', 'notes']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['gosi_eligible', 'gosi_number', 'bank_name', 'bank_account', 'overtime_rate']);
        });
    }
};
