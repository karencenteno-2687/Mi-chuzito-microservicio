<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('address')->nullable();          // RF-025: direccion
            $table->string('city')->nullable();
            $table->string('department')->nullable();
            $table->string('avatar')->nullable();           // RF-025: foto de perfil
            $table->text('preferences')->nullable();        // RF-025: preferencias
            $table->string('document_type')->nullable();    // RF-025: tipo documento
            $table->string('document_number')->nullable();  // RF-025: numero documento
            $table->date('birth_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};