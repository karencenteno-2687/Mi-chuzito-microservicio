<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('method');                        // efectivo, transferencia
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pendiente'); // pendiente, aprobado, rechazado
            $table->string('reference')->nullable();         // referencia de transferencia
            $table->text('encrypted_data')->nullable();      // RF-006: datos encriptados
            $table->string('transaction_token')->nullable(); // RF-006: token de autenticacion
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};