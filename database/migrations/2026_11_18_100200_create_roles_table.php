<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name')->unique();
            $table->decimal('max_discount_percentage', 5, 2);
            $table->boolean('can_view_cost')->default(false);
            $table->boolean('can_void')->default(false);
            $table->boolean('can_refund')->default(false);
            $table->boolean('can_adjust_stock')->default(false);
            $table->boolean('can_manage_purchases')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
};
