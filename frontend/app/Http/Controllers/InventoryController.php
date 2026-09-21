<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('stock')->get();

        $supplies = Supply::orderBy('category')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Inventory', [
            'products' => $products,
            'supplies' => $supplies,

            'agotados' => $products->where('stock', 0)->count(),

            'bajos' => $products
                ->where('stock', '>', 0)
                ->where('stock', '<=', 5)
                ->count(),

            'insumosBajos' => $supplies->filter(function ($supply) {
                return $supply->stock <= $supply->minimum_stock;
            })->count(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'stock'        => 'required|integer|min:0',
            'is_available' => 'required|boolean',
            'price'        => 'required|numeric|min:0',
            'image'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $data = [
            'stock'        => $request->stock,
            'is_available' => $request->stock > 0 ? $request->is_available : false,
            'price'        => $request->price,
        ];

        if ($request->hasFile('image')) {

            if ($product->image_url && str_starts_with($product->image_url, 'productos/')) {
                Storage::disk('public')->delete($product->image_url);
            }

            $path = $request->file('image')->store('productos', 'public');
            $data['image_url'] = $path;
        }

        $product->update($data);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->withProperties([
                'stock' => $request->stock,
                'is_available' => $request->is_available
            ])
            ->log('inventory_updated');

        return back()->with('success', 'Inventario actualizado.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'category'    => 'required|string|max:100',
            'stock'       => 'required|integer|min:0',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'image.required' => 'Debes subir una imagen del producto.',
            'image.mimes'    => 'Solo se permiten imágenes JPG, JPEG, PNG o WEBP.',
            'image.max'      => 'La imagen no debe superar 4MB.',
        ]);

        $path = $request->file('image')->store('productos', 'public');

        $product = Product::create([
            'name'         => $request->name,
            'description'  => $request->description,
            'price'        => $request->price,
            'category'     => $request->category,
            'stock'        => $request->stock,
            'image_url'    => $path,
            'is_available' => $request->stock > 0,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->log('product_created');

        return back()->with('success', 'Producto creado correctamente.');
    }

    public function destroy(Product $product)
    {
        if ($product->image_url && str_starts_with($product->image_url, 'productos/')) {
            Storage::disk('public')->delete($product->image_url);
        }

        $product->delete();

        activity()
            ->causedBy(auth()->user())
            ->log('product_deleted');

        return back()->with('success', 'Producto eliminado.');
    }
}