<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('license_keys', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->char('key_hash', 64)->unique();
            $table->string('key_hint', 25);
            $table->text('notes')->nullable();
            $table->unsignedInteger('duration_seconds');
            $table->timestamp('expires_at')->index();
            $table->char('hwid_hash', 64)->nullable();
            $table->text('hwid')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('bound_at')->nullable();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->unsignedInteger('reset_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_keys');
    }
};
