<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['license_products', 'purchase_orders'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->unsignedInteger('duration_seconds')->nullable();
            });
            DB::table($name)->update(['duration_seconds' => DB::raw('days * 86400')]);
        }
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->string('product_description', 500)->nullable();
            $table->json('game_snapshot')->nullable();
            $table->string('payment_provider', 100)->nullable();
            $table->string('payer_name', 100)->nullable();
            $table->text('payment_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason', 500)->nullable();
            $table->unsignedBigInteger('owner_license_id')->nullable()->change();
            $table->string('purpose', 15)->default('purchase')->index();
            $table->foreignId('target_license_id')->nullable()->constrained('license_keys');
            $table->string('target_key_hint', 80)->nullable();
            $table->string('customer_contact', 100)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->string('guest_token_hash', 64)->nullable()->index();
            $table->string('gateway_payment_id', 100)->nullable()->unique();
            $table->string('gateway_mode', 15)->nullable();
            $table->string('gateway_status', 30)->nullable();
            $table->text('gateway_payment_url')->nullable();
            $table->unsignedBigInteger('gateway_fee')->nullable();
            $table->dateTime('gateway_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropConstrainedForeignId('target_license_id');
            $table->dropColumn(['purpose', 'target_key_hint', 'customer_contact', 'customer_email', 'guest_token_hash', 'gateway_payment_id', 'gateway_mode', 'gateway_status', 'gateway_payment_url', 'gateway_fee', 'gateway_expires_at']);
            $table->dropColumn(['product_description', 'game_snapshot', 'payment_provider', 'payer_name', 'payment_notes', 'verified_at', 'cancelled_at', 'cancellation_reason', 'duration_seconds']);
        });
        Schema::table('license_products', fn (Blueprint $table) => $table->dropColumn('duration_seconds'));
    }
};
