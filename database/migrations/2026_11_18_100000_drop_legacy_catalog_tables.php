<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The pizza shop tables occupy products and categories.
     * Drop them so the POS schema can create those names fresh.
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('order_lists');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->integer('category_id');
                $table->string('name');
                $table->longText('description');
                $table->string('image')->nullable();
                $table->integer('price');
                $table->integer('waiting_time');
                $table->integer('view_count')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->integer('product_id');
                $table->integer('qty');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->string('order_code');
                $table->string('total_price');
                $table->integer('status')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ratings')) {
            Schema::create('ratings', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->integer('product_id');
                $table->integer('rating_count');
                $table->longText('message')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_lists')) {
            Schema::create('order_lists', function (Blueprint $table) {
                $table->id();
                $table->integer('user_id');
                $table->integer('product_id');
                $table->integer('qty');
                $table->integer('total');
                $table->string('order_code');
                $table->timestamps();
            });
        }
    }
};
