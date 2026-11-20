<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('shop_id')->constrained('shops');
            $table->foreignUlid('terminal_id')->nullable()->constrained('terminals')->nullOnDelete();
            $table->foreignUlid('actor_user_id')->constrained('users');
            $table->foreignUlid('approved_by')->nullable()->constrained('users');
            $table->enum('action', [
                'discount_override',
                'void_sale',
                'refund',
                'stock_adjustment',
                'price_change',
            ]);
            $table->string('entity_type');
            $table->char('entity_id', 26);
            $table->text('reason')->nullable();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('sync_cursors', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('terminal_id')->constrained('terminals');
            $table->string('stream');
            $table->string('cursor')->nullable();
            $table->timestamps();

            $table->unique(['terminal_id', 'stream']);
        });

        Schema::create('sync_processed_keys', function (Blueprint $table) {
            $table->string('idempotency_key')->primary();
            $table->foreignUlid('terminal_id')->constrained('terminals');
            $table->timestamp('processed_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sync_processed_keys');
        Schema::dropIfExists('sync_cursors');
        Schema::dropIfExists('audit_logs');
    }
};
