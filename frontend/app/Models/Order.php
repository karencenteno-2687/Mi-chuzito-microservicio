<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa un pedido dentro del sistema.
 *
 * Este modelo almacena toda la información relacionada con un
 * pedido realizado por un cliente, incluyendo los productos,
 * el estado del pedido, el método de pago, el repartidor
 * asignado y las fechas de seguimiento.
 */
class Order extends Model
{
    /**
     * Campos que pueden asignarse masivamente.
     *
     * @var array
     */
    protected $fillable = [
    'user_id',
    'repartidor_id',
    'vendedor_id',
    'status',
    'items',
    'subtotal',
    'tax',
    'total',
    'address',
    'notes',
    'payment_method',
    'assigned_at',
    'ready_at',
    'picked_up_at',
    'delivered_at',
    'cancelled_at',
];

    /**
     * Conversión automática de atributos.
     *
     * Laravel convierte estos campos al tipo de dato
     * correspondiente al obtenerlos desde la base de datos.
     *
     * @var array
     */
   protected $casts = [
    'items'        => 'array',
    'subtotal'     => 'decimal:2',
    'tax'          => 'decimal:2',
    'total'        => 'decimal:2',
    'assigned_at'  => 'datetime',
    'ready_at'     => 'datetime',
    'picked_up_at' => 'datetime',
    'delivered_at' => 'datetime',
    'cancelled_at' => 'datetime',
];

    /**
     * Estados permitidos para un pedido.
     *
     * Estos estados son utilizados durante el ciclo
     * de vida del pedido.
     */
    const STATUSES = [
        'en_proceso' => 'En proceso',
        'en_camino'  => 'En camino',
        'entregado'  => 'Entregado',
        'cancelado'  => 'Cancelado',
    ];

    /**
     * Relación con el cliente.
     *
     * Un pedido pertenece a un único cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el repartidor.
     *
     * Un pedido puede estar asignado a un repartidor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function repartidor()
    {
        return $this->belongsTo(User::class, 'repartidor_id');
    }

    /**
     * Relación con el vendedor.
     *
     * Un pedido puede estar asociado a un vendedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    /**
     * RELACIÓN:        Order tiene un Payment (hasOne, 1 pedido → 1 pago)
     * USADA DESDE:     PaymentController (próximo paso) y las vistas Vue de
     *                  pedidos, para saber si un pedido ya fue pagado o no
     * PERMITE:         $order->payment  →  accede al pago asociado (o null si aún no paga)
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Verifica si un pedido puede cambiar
     * de un estado a otro.
     *
     * Evita cambios inválidos como pasar
     * directamente de "En proceso" a "Entregado".
     *
     * @param string $newStatus Nuevo estado solicitado.
     * @return bool
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = [
            'en_proceso' => ['en_camino', 'cancelado'],
            'en_camino'  => ['entregado'],
            'entregado'  => [],
            'cancelado'  => [],
        ];

        return in_array($newStatus, $allowed[$this->status] ?? []);
    }

    /**
     * Scope para obtener únicamente
     * los pedidos de un cliente.
     *
     * @param $query
     * @param int $userId
     * @return mixed
     */
    public function scopeForCliente($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para obtener únicamente
     * los pedidos asignados a un repartidor.
     *
     * @param $query
     * @param int $userId
     * @return mixed
     */
    public function scopeForRepartidor($query, $userId)
    {
        return $query->where('repartidor_id', $userId);
    }

    /**
     * Scope para obtener los pedidos
     * disponibles para un vendedor.
     *
     * Incluye pedidos asignados al vendedor
     * y pedidos sin vendedor que aún están
     * en estado "En proceso".
     *
     * @param $query
     * @param int $userId
     * @return mixed
     */
    public function scopeForVendedor($query, $userId)
    {
        return $query->where('vendedor_id', $userId)
                     ->orWhereNull('vendedor_id')
                     ->where('status', 'en_proceso');
    }
}