<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa una factura dentro del sistema.
 *
 * Este modelo almacena la información de las facturas
 * generadas después de confirmar un pago, incluyendo
 * datos del cliente, valores de la compra y la relación
 * con el usuario y el pago correspondiente.
 */
class Invoice extends Model
{
    /**
     * Campos que pueden asignarse masivamente.
     *
     * Estos atributos pueden ser llenados utilizando
     * métodos como create() o update().
     *
     * @var array
     */
    protected $fillable = [
        'payment_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'tax',
        'total',
        'status',
        'items',
        'client_name',
        'client_email',
        'client_phone',
        'client_document',
        'issued_at',
    ];

    /**
     * Conversión automática de atributos.
     *
     * Laravel transforma estos campos al tipo indicado
     * cuando son consultados desde la base de datos.
     *
     * @var array
     */
    protected $casts = [
        'subtotal'  => 'decimal:2',
        'tax'       => 'decimal:2',
        'total'     => 'decimal:2',
        'items'     => 'array',
        'issued_at' => 'datetime',
    ];

    /**
     * Relación muchos a uno con Payment.
     *
     * Una factura pertenece a un único pago.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Relación muchos a uno con User.
     *
     * Una factura pertenece a un único usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Genera un número de factura consecutivo y único.
     *
     * Busca la última factura registrada, incrementa el
     * consecutivo y devuelve un código con el formato:
     *
     * FAC-000001
     * FAC-000002
     * FAC-000003
     *
     * @return string Número de factura generado.
     */
    public static function generateInvoiceNumber(): string
    {
        // Obtiene la última factura registrada.
        $last = self::orderBy('id', 'desc')->first();

        // Si existe una factura, incrementa el consecutivo.
        // Si no existe, comienza desde 1.
        $number = $last
            ? (int) substr($last->invoice_number, 4) + 1
            : 1;

        // Devuelve el número con el formato FAC-000001.
        return 'FAC-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}