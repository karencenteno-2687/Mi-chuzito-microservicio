<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Modelo que representa un producto del menú.
 *
 * Es la entidad central de tres módulos: Gestión de Menú (lo muestra
 * al público), Entregas (su stock se descuenta al crear un pedido) y
 * Reportes (alimenta el Reporte de Inventario y el de Ventas).
 */
class Product extends Model
{
    /**
     * Campos que pueden asignarse masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'category',
        'is_available',
        'stock',
    ];

    /**
     * Conversión automática de atributos.
     *
     * - is_available funciona como un interruptor derivado del stock:
     *   se apaga automáticamente cuando stock llega a 0 (ver
     *   OrderController::store), aunque también puede desactivarse
     *   manualmente desde el panel de inventario.
     *
     * @var array
     */
    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
        'stock'        => 'integer',
    ];

    /**
     * Atributo calculado (accessor) que se agrega automáticamente
     * a cada producto serializado, gracias a $appends.
     *
     * @var array
     */
    protected $appends = ['image_url_full'];

    /**
     * Devuelve la URL completa de la imagen del producto.
     *
     * Resuelve dos casos posibles del campo image_url:
     * - Si ya es una URL externa (empieza por "http"), se devuelve tal cual.
     * - Si es una ruta local guardada en el storage de Laravel, se
     *   construye la URL pública con Storage::url().
     *
     * Esto le evita al frontend (Vue) tener que saber de dónde viene
     * cada imagen; siempre recibe una URL lista para usar en <img src>.
     *
     * @return string URL completa de la imagen, o cadena vacía si el
     *         producto no tiene imagen asignada.
     */
    public function getImageUrlFullAttribute(): string
    {
        if (!$this->image_url) {
            return '';
        }

        if (str_starts_with($this->image_url, 'http')) {
            return $this->image_url;
        }

        return Storage::url($this->image_url);
    }
}
