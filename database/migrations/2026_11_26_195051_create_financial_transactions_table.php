<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $t): void {
            $t->id();
            $t->string('reference', 80)->unique();
            $t->unsignedBigInteger('order_id')->nullable()->unique();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('direction', 10);
            $t->string('source', 20)->default('manual');
            $t->string('external_reference', 100)->nullable();
            $t->unique(['source', 'external_reference']);
            $t->string('status', 12)->default('posted');
            $t->unsignedBigInteger('amount');
            $t->unsignedBigInteger('fee')->default(0);
            $t->string('currency', 3)->default('IDR');
            $t->string('description', 255);
            $t->dateTime('paid_at')->index();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
