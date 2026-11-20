<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->foreignUlid('variant_id')->constrained('product_variants');
            $table->decimal('qty_on_hand', 14, 3)->default(0);
            $table->timestamps();

            $table->unique(['shop_id', 'variant_id']);
        });

        Schema::create('stock_lots', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('variant_id')->constrained('product_variants');
            $table->string('lot_code')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('qty_on_hand', 14, 3)->default(0);
            $table->timestamps();

            $table->index('expiry_date');
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->foreignUlid('variant_id')->constrained('product_variants');
            $table->foreignUlid('lot_id')->nullable()->constrained('stock_lots');
            $table->enum('movement_type', [
                'purchase_in',
                'sale_out',
                'sale_return',
                'purchase_return',
                'damage',
                'loss',
                'audit_adjustment',
                'bundle_component_out',
                'opening',
            ]);
            $table->decimal('qty_delta', 14, 3);
            $table->decimal('qty_after', 14, 3);
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->string('reference_type')->nullable();
            $table->char('reference_id', 26)->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('occurred_at');
            $table->foreignUlid('created_by')->constrained('users');
            $table->foreignUlid('approved_by')->nullable()->constrained('users');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['variant_id', 'occurred_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_lots');
        Schema::dropIfExists('stock_balances');
    }
};
