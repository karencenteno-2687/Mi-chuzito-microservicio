<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('disponible')->default(true)->after('is_verified'); // RF-038
            $table->timestamp('disponible_updated_at')->nullable()->after('disponible');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['disponible', 'disponible_updated_at']);
        });
    }
};