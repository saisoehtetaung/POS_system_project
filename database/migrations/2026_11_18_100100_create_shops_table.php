<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->char('currency_code', 3)->default('MMK');
            $table->decimal('tax_rate', 8, 4)->default(0);
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shops');
    }
};
