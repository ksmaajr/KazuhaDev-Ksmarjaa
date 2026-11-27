<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $t): void {
            $t->string('banner_path')->nullable();
            $t->string('logo_path')->nullable();
        });
        Schema::table('license_keys', fn (Blueprint $t) => $t->string('member_name', 100)->nullable());
        DB::table('license_keys')->update(['member_name' => DB::raw('label')]);
    }

    public function down(): void
    {
        Schema::table('games', fn (Blueprint $t) => $t->dropColumn(['banner_path', 'logo_path']));
        Schema::table('license_keys', fn (Blueprint $t) => $t->dropColumn('member_name'));
    }
};
