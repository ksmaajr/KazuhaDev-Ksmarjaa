<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_keys', function (Blueprint $table): void {
            $table->text('key_value')->nullable();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('license_keys', function (Blueprint $table): void {
            $table->dropColumn('key_value');
            $table->dropSoftDeletes();
        });
    }
};
