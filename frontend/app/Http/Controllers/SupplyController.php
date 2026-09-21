<?php

namespace App\Http\Controllers;

use App\Models\Supply;
use Inertia\Inertia;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.inventory');
    }

    public function store(Request $request)
    {
        if ($request->has('cost')) {
            $cleanCost = preg_replace('/[^\d]/', '', (string)$request->cost);
            $request->merge(['cost' => $cleanCost !== '' ? (int)$cleanCost : null]);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'category'      => 'required|string|max:100',
            'unit'          => 'required|string|max:50',
            'cost'          => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ], [
            'name.required'          => 'El nombre del insumo es obligatorio.',
            'category.required'      => 'La categoría es obligatoria.',
            'unit.required'          => 'La unidad de medida es obligatoria.',
            'cost.required'          => 'El precio de costo es obligatorio.',
            'cost.numeric'           => 'El precio de costo debe ser un valor numérico.',
            'stock.required'         => 'El stock inicial es obligatorio.',
            'stock.integer'          => 'El stock inicial debe ser un número entero (sin decimales).',
            'minimum_stock.required' => 'El stock mínimo es obligatorio.',
            'minimum_stock.integer'  => 'El stock mínimo debe ser un número entero (sin decimales).',
        ]);

        $supply = Supply::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($supply)
            ->withProperties($validated)
            ->log('supply_created');

        return back()->with('success', 'Insumo creado correctamente.');
    }

    public function update(Request $request, Supply $supply)
    {
        if ($request->has('cost')) {
            $cleanCost = preg_replace('/[^\d]/', '', (string)$request->cost);
            $request->merge(['cost' => $cleanCost !== '' ? (int)$cleanCost : null]);
        }

        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'category'      => 'required|string|max:100',
            'unit'          => 'required|string|max:50',
            'cost'          => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
        ], [
            'name.required'          => 'El nombre del insumo es obligatorio.',
            'category.required'      => 'La categoría es obligatoria.',
            'unit.required'          => 'La unidad de medida es obligatoria.',
            'cost.required'          => 'El precio de costo es obligatorio.',
            'cost.numeric'           => 'El precio de costo debe ser un valor numérico.',
            'stock.required'         => 'El stock es obligatorio.',
            'stock.integer'          => 'El stock debe ser un número entero (sin decimales).',
            'minimum_stock.required' => 'El stock mínimo es obligatorio.',
            'minimum_stock.integer'  => 'El stock mínimo debe ser un número entero (sin decimales).',
        ]);

        $supply->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($supply)
            ->withProperties($validated)
            ->log('supply_updated');

        return back()->with('success', 'Insumo actualizado correctamente.');
    }

    public function destroy(Supply $supply)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($supply)
            ->log('supply_deleted');

        $supply->delete();

        return back()->with('success', 'Insumo eliminado.');
    }
}