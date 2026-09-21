<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->nullable()->after('email');
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->boolean('is_active')->default(true)->after('two_factor_secret');
            $table->softDeletes()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'two_factor_enabled', 'two_factor_secret', 'is_active', 'deleted_at']);
        });
    }
};