<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_head_office')->default(false);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('role', 30)->default('user')->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no', 30)->unique();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->string('job_title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'role', 'is_active']);
        });
        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');
    }
};
