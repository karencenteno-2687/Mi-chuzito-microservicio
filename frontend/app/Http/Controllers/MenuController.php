<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controlador del menú público.
 *
 * Es la puerta de entrada de la aplicación (ruta "/"): muestra a
 * cualquier visitante el catálogo de productos que el negocio tiene
 * disponibles en este momento, sin requerir autenticación.
 */
class MenuController extends Controller
{
    /**
     * Muestra el catálogo público de productos.
     *
     * Solo se listan productos con is_available = true. Un producto
     * deja de aparecer aquí automáticamente cuando su stock llega a 0
     * (esa regla vive en OrderController::store, al confirmarse un
     * pedido) o cuando el administrador lo desactiva manualmente
     * desde el módulo de inventario.
     *
     * @return \Inertia\Response Vista Menu/Index con los productos
     *         disponibles (ordenados por categoría) y la lista de
     *         categorías únicas presentes entre ellos.
     */
    public function index()
    {
        $products = Product::where('is_available', true)
            ->orderBy('category')
            ->get();

        // Se extraen solo las categorías que realmente tienen productos
        // disponibles, para no mostrar categorías vacías en los filtros
        // del frontend.
        $categories = $products->pluck('category')->unique()->values();

        return Inertia::render('Menu/Index', [
            'products'   => $products,
            'categories' => $categories,
        ]);
    }
}
