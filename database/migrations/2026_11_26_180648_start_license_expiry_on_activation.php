<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_keys', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->change();
        });
        DB::table('license_keys')->orderBy('id')->chunkById(100, function ($licenses): void {
            foreach ($licenses as $license) {
                DB::table('license_keys')->where('id', $license->id)->update(['expires_at' => $license->activated_at ? Carbon::parse($license->activated_at)->addSeconds($license->duration_seconds) : null]);
            }
        });
    }

    public function down(): void
    {
        DB::table('license_keys')->whereNull('expires_at')->orderBy('id')->chunkById(100, function ($licenses): void {
            foreach ($licenses as $license) {
                DB::table('license_keys')->where('id', $license->id)->update(['expires_at' => Carbon::parse($license->created_at)->addSeconds($license->duration_seconds)]);
            }
        });
        Schema::table('license_keys', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable(false)->change();
        });
    }
};
