<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentApiController extends Controller
{
    // GET /api/payments - pagos del usuario autenticado
    public function index(Request $request)
    {
        $payments = $request->user()
            ->payments()
            ->with('invoice')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $payments,
        ]);
    }

    // GET /api/payments/{id}
    public function show(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $payment->load('invoice'),
        ]);
    }

    // POST /api/payments - crear pago
    public function store(Request $request)
    {
        $request->validate([
            'method'    => 'required|in:efectivo,transferencia',
            'amount'    => 'required|numeric|min:0.01',
            'reference' => 'required_if:method,transferencia|nullable|string',
        ]);

        $user  = $request->user();
        $token = Str::random(64);

        $payment = Payment::create([
            'user_id'           => $user->id,
            'method'            => $request->method,
            'amount'            => $request->amount,
            'status'            => $request->method === 'efectivo' ? 'aprobado' : 'pendiente',
            'reference'         => $request->reference,
            'transaction_token' => hash('sha256', $token),
            'paid_at'           => $request->method === 'efectivo' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pago registrado correctamente.',
            'data'    => $payment,
        ], 201);
    }
}