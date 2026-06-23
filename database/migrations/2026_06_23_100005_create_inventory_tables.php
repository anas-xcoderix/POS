<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('part_id')->constrained();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('reserved_qty', 14, 2)->default(0);
            $table->decimal('avg_cost', 14, 4)->default(0);
            $table->timestamps();
            $table->unique(['branch_id', 'location_id', 'part_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('part_id')->constrained();
            $table->string('movement_type', 30);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_no', 50)->nullable();
            $table->decimal('quantity_in', 14, 2)->default(0);
            $table->decimal('quantity_out', 14, 2)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('balance_after', 14, 2)->default(0);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('movement_date');
            $table->timestamps();
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 30)->unique();
            $table->foreignId('from_branch_id')->constrained('branches');
            $table->foreignId('to_branch_id')->constrained('branches');
            $table->date('transfer_date');
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->constrained();
            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');
            $table->decimal('quantity', 14, 2);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_balances');
    }
};
