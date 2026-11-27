<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $t): void {
            $t->id();
            $t->string('number', 40)->unique();
            $t->uuid('idempotency_key')->unique();
            $t->foreignId('owner_license_id')->constrained('license_keys');
            $t->foreignId('license_product_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('issued_license_id')->nullable()->constrained('license_keys');
            $t->string('member_name', 100);
            $t->string('product_name', 100);
            $t->unsignedTinyInteger('days');
            $t->json('game_ids');
            $t->unsignedBigInteger('amount');
            $t->string('currency', 3)->default('IDR');
            $t->string('status', 15)->default('pending')->index();
            $t->string('payment_method', 20)->default('manual');
            $t->string('payment_reference', 100)->nullable();
            $t->dateTime('paid_at')->nullable();
            $t->timestamps();
        });
        Schema::table('financial_transactions', fn (Blueprint $t) => $t->foreign('order_id')->references('id')->on('purchase_orders'));
    }

    public function down(): void
    {
        Schema::table('financial_transactions', fn (Blueprint $t) => $t->dropForeign(['order_id']));
        Schema::dropIfExists('purchase_orders');
    }
};
