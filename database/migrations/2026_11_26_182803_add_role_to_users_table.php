<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 16)->default('member')->index();
        });
        DB::table('users')->update(['role' => 'admin']);
        Schema::table('license_events', function (Blueprint $table): void {
            $table->json('metadata')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('license_events', function (Blueprint $table): void {
            $table->dropColumn('metadata');
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
