<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');         // cliente
            $table->foreignId('repartidor_id')->nullable()->constrained('users');     // RF-013
            $table->foreignId('vendedor_id')->nullable()->constrained('users');       // RF-033
            $table->string('status')->default('en_proceso');                          // RF-003: en_proceso, en_camino, entregado, cancelado
            $table->json('items');                                                    // productos del carrito
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);                               // IVA 19%
            $table->decimal('total', 10, 2)->default(0);
            $table->string('address')->nullable();                                    // direccion de entrega
            $table->text('notes')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamp('assigned_at')->nullable();                             // RF-013
            $table->timestamp('picked_up_at')->nullable();                            // RF-039
            $table->timestamp('delivered_at')->nullable();                            // RF-015
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};