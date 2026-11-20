<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->enum('customer_type', ['regular', 'b2b'])->default('regular');
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->decimal('balance', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUlid('origin_terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['shop_id', 'phone']);
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->foreignUlid('terminal_id')->constrained('terminals');
            $table->foreignUlid('user_id')->constrained('users');
            $table->enum('status', ['open', 'closed']);
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_cash', 14, 2);
            $table->decimal('expected_cash', 14, 2)->nullable();
            $table->decimal('actual_cash', 14, 2)->nullable();
            $table->decimal('difference', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE shifts ADD COLUMN open_slot CHAR(26) AS (CASE WHEN status = 'open' THEN terminal_id ELSE NULL END) STORED");
        DB::statement('CREATE UNIQUE INDEX shifts_one_open_per_terminal ON shifts (open_slot)');

        Schema::create('cash_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shift_id')->constrained('shifts');
            $table->enum('type', ['cash_in', 'cash_out', 'sale', 'refund']);
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->string('reference_type')->nullable();
            $table->char('reference_id', 26)->nullable();
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->foreignUlid('terminal_id')->constrained('terminals');
            $table->foreignUlid('shift_id')->nullable()->constrained('shifts');
            $table->foreignUlid('customer_id')->nullable()->constrained('customers');
            $table->foreignUlid('cashier_id')->constrained('users');
            $table->string('sale_no')->unique();
            $table->enum('status', ['parked', 'completed', 'voided', 'refunded']);
            $table->enum('price_tier', ['retail', 'wholesale']);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->decimal('amount_due', 14, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'credit']);
            $table->timestamp('sold_at');
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->foreignUlid('approved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['shop_id', 'sold_at']);
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUlid('variant_id')->nullable()->constrained('product_variants');
            $table->foreignUlid('product_id')->constrained('products');
            $table->string('description');
            $table->decimal('quantity_base', 14, 3);
            $table->decimal('unit_price', 14, 2);
            $table->enum('price_tier', ['retail', 'wholesale']);
            $table->decimal('discount_amount', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2);
            $table->boolean('is_bundle')->default(false);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('sale_id')->nullable()->constrained('sales');
            $table->foreignUlid('customer_id')->nullable()->constrained('customers');
            $table->foreignUlid('shift_id')->constrained('shifts');
            $table->enum('method', ['cash', 'card', 'transfer', 'credit']);
            $table->decimal('amount', 14, 2);
            $table->decimal('tendered', 14, 2)->nullable();
            $table->decimal('change_amount', 14, 2)->nullable();
            $table->timestamp('paid_at');
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('customer_ledger', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('customer_id')->constrained('customers');
            $table->foreignUlid('sale_id')->nullable()->constrained('sales');
            $table->foreignUlid('payment_id')->nullable()->constrained('payments');
            $table->enum('entry_type', ['credit_sale', 'repayment', 'adjustment']);
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->decimal('balance_after', 14, 2);
            $table->date('due_date')->nullable();
            $table->timestamp('occurred_at');
            $table->foreignUlid('created_by')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_ledger');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('cash_movements');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('customers');
    }
};
