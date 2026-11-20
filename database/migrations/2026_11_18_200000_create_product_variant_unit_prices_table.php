<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_variant_unit_prices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignUlid('unit_id')->constrained('units');
            $table->decimal('price', 14, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['variant_id', 'unit_id']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->char('unit_id', 26)->nullable()->after('product_id');
            $table->decimal('quantity', 14, 3)->nullable()->after('description');
            $table->decimal('factor_to_base', 14, 6)->nullable()->after('quantity');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'quantity', 'factor_to_base']);
        });

        Schema::dropIfExists('product_variant_unit_prices');
    }
};
