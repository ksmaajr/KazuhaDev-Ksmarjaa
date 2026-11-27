<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_license_key', function (Blueprint $table): void {
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->foreignId('license_key_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['game_id', 'license_key_id']);
        });
        $gameId = DB::table('games')->where('code', 'rules-of-survival')->value('id');
        DB::table('license_keys')->orderBy('id')->chunkById(500, function ($licenses) use ($gameId): void {
            DB::table('game_license_key')->insert($licenses->map(fn ($license) => ['game_id' => $gameId, 'license_key_id' => $license->id, 'created_at' => now(), 'updated_at' => now()])->all());
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_license_key');
    }
};
