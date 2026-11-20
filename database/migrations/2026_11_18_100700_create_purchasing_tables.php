<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUlid('origin_terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->foreignUlid('supplier_id')->constrained('suppliers');
            $table->foreignUlid('terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->string('purchase_no')->unique();
            $table->enum('status', ['draft', 'received', 'cancelled']);
            $table->enum('payment_type', ['cash', 'credit']);
            $table->timestamp('purchased_at');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignUlid('variant_id')->constrained('product_variants');
            $table->foreignUlid('unit_id')->constrained('units');
            $table->decimal('quantity', 14, 3);
            $table->decimal('factor_to_base', 14, 6);
            $table->decimal('quantity_base', 14, 3);
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('line_total', 14, 2);
            $table->date('expiry_date')->nullable();
            $table->string('lot_code')->nullable();
            $table->timestamps();
        });

        Schema::create('supplier_price_history', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('supplier_id')->constrained('suppliers');
            $table->foreignUlid('variant_id')->constrained('product_variants');
            $table->foreignUlid('unit_id')->constrained('units');
            $table->decimal('unit_cost', 14, 2);
            $table->foreignUlid('purchase_item_id')->constrained('purchase_items');
            $table->timestamp('effective_at');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('supplier_id')->constrained('suppliers');
            $table->foreignUlid('purchase_id')->nullable()->constrained('purchases');
            $table->decimal('amount', 14, 2);
            $table->string('method');
            $table->string('reference')->nullable();
            $table->timestamp('paid_at');
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('supplier_price_history');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('suppliers');
    }
};
