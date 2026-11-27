<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 80)->unique();
            $table->boolean('is_active')->default(true);
            $table->string('release_version', 50)->nullable();
            foreach (['injector', 'dll'] as $kind) {
                $table->string($kind.'_path')->nullable();
                $table->string($kind.'_name')->nullable();
                $table->string($kind.'_sha256', 64)->nullable();
                $table->unsignedBigInteger($kind.'_size')->nullable();
            }
            $table->timestamps();
        });
        DB::table('games')->insert(['name' => 'Rules Of Survival', 'code' => 'rules-of-survival', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
