<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->char('parent_id', 26)->nullable();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shop_id', 'slug']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shop_id', 'name']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->string('name');
            $table->string('abbreviation');
            $table->timestamps();

            $table->unique(['shop_id', 'abbreviation']);
        });

        Schema::create('variant_attributes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->string('name');
            $table->timestamps();

            $table->unique(['shop_id', 'name']);
        });

        Schema::create('variant_attribute_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attribute_id')->constrained('variant_attributes')->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();

            $table->unique(['attribute_id', 'value']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->foreignUlid('category_id')->constrained('categories');
            $table->foreignUlid('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignUlid('base_unit_id')->constrained('units');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->enum('product_type', ['standard', 'bundle'])->default('standard');
            $table->boolean('track_stock')->default(true);
            $table->boolean('track_expiry')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignUlid('origin_terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained('products');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->decimal('retail_price', 14, 2);
            $table->decimal('wholesale_price', 14, 2);
            $table->decimal('min_wholesale_qty', 14, 3)->nullable();
            $table->decimal('cost_price', 14, 2);
            $table->decimal('reorder_level', 14, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUlid('origin_terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_variant_values', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignUlid('attribute_value_id')->constrained('variant_attribute_values')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['variant_id', 'attribute_value_id']);
        });

        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('unit_id')->constrained('units');
            $table->decimal('factor_to_base', 14, 6);
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });

        Schema::create('bundle_components', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('bundle_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUlid('component_variant_id')->constrained('product_variants');
            $table->decimal('quantity_base', 14, 3);
            $table->timestamps();

            $table->unique(['bundle_product_id', 'component_variant_id']);
        });

        Schema::create('label_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->string('name');
            $table->decimal('width_mm', 6, 2);
            $table->decimal('height_mm', 6, 2);
            $table->boolean('show_shop_name')->default(true);
            $table->boolean('show_item_name')->default(true);
            $table->boolean('show_price')->default(true);
            $table->string('barcode_format')->default('code128');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('label_templates');
        Schema::dropIfExists('bundle_components');
        Schema::dropIfExists('unit_conversions');
        Schema::dropIfExists('product_variant_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('variant_attribute_values');
        Schema::dropIfExists('variant_attributes');
        Schema::dropIfExists('units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
    }
};
