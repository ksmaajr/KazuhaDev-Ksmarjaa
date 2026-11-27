<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_products', function (Blueprint $t): void {
            $t->id();
            $t->string('name', 100);
            $t->string('description', 500)->nullable();
            $t->unsignedTinyInteger('days');
            $t->unsignedBigInteger('price');
            $t->json('game_ids');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_products');
    }
};
