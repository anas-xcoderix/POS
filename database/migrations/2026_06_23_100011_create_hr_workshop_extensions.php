<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('aqama_no', 30)->nullable()->after('job_title');
            $table->date('aqama_expiry')->nullable()->after('aqama_no');
            $table->string('license_no', 30)->nullable()->after('aqama_expiry');
            $table->date('license_expiry')->nullable()->after('license_no');
            $table->decimal('housing_allowance', 12, 2)->default(0)->after('basic_salary');
            $table->decimal('transport_allowance', 12, 2)->default(0)->after('housing_allowance');
        });

        Schema::table('job_cards', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('vehicle_id')->constrained()->nullOnDelete();
            $table->foreignId('sales_invoice_id')->nullable()->after('mechanic_id')->constrained()->nullOnDelete();
        });

        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('status', 20)->default('present');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'attendance_date']);
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_no', 30)->unique();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->string('status', 20)->default('draft');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
            $table->unique(['branch_id', 'period_month', 'period_year']);
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('housing_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->unsignedTinyInteger('days_present')->default(0);
            $table->unsignedTinyInteger('days_absent')->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('attendance_records');

        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('location_id');
            $table->dropConstrainedForeignId('sales_invoice_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'aqama_no', 'aqama_expiry', 'license_no', 'license_expiry',
                'housing_allowance', 'transport_allowance',
            ]);
        });
    }
};
