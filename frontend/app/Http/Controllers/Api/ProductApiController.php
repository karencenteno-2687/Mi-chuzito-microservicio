<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    // GET /api/products - listado publico del menu
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->has('category') && $request->category !== 'Todos') {
            $query->where('category', $request->category);
        }

        if ($request->has('available')) {
            $query->where('is_available', filter_var($request->available, FILTER_VALIDATE_BOOLEAN));
        }

        $products = $query->orderBy('category')->get();

        return response()->json([
            'success' => true,
            'data'    => $products,
            'count'   => $products->count(),
        ]);
    }

    // GET /api/products/{id}
    public function show(Product $product)
    {
        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }

    // GET /api/products/categories/list
    public function categories()
    {
        $categories = Product::select('category')->distinct()->pluck('category');

        return response()->json([
            'success' => true,
            'data'    => $categories,
        ]);
    }

    // POST /api/products - solo admin, requiere token Sanctum
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'category'    => 'required|string|max:100',
            'image_url'   => 'nullable|url',
            'stock'       => 'required|integer|min:0',
        ]);

        $product = Product::create([
            'name'         => $request->name,
            'description'  => $request->description,
            'price'        => $request->price,
            'category'     => $request->category,
            'image_url'    => $request->image_url,
            'stock'        => $request->stock,
            'is_available' => $request->stock > 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado correctamente.',
            'data'    => $product,
        ], 201);
    }

    // PUT /api/products/{id} - solo admin
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name'         => 'sometimes|string|max:150',
            'description'  => 'nullable|string',
            'price'        => 'sometimes|numeric|min:0',
            'category'     => 'sometimes|string|max:100',
            'image_url'    => 'nullable|url',
            'stock'        => 'sometimes|integer|min:0',
            'is_available' => 'sometimes|boolean',
        ]);

        $product->update($request->only([
            'name', 'description', 'price', 'category', 'image_url', 'stock', 'is_available'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado correctamente.',
            'data'    => $product,
        ]);
    }

    // DELETE /api/products/{id} - solo admin
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado correctamente.',
        ]);
    }
}