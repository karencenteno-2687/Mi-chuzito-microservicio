<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Order;
use App\Notifications\PaymentConfirmedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->payment) {
            return redirect()->route('payments.invoice', $order->payment->invoice->id)
                ->with('success', 'Este pedido ya fue pagado.');
        }

        return Inertia::render('Payments/Create', [
            'user'  => $request->user()->load('profile'),
            'order' => $order,
        ]);
    }

    /**
     * RUTA:  POST /pagos/{order}
     * Unico metodo que llega por este formulario: 'efectivo'.
     * PayPal usa su propio flujo en PayPalController (checkout/success).
     * CAMBIO: el pago en efectivo YA NO se aprueba automaticamente;
     * queda 'pendiente' hasta que un administrador lo confirme (approve()).
     */
    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_if($order->payment, 409, 'Este pedido ya fue pagado.');

        $request->validate([
            'method' => 'required|in:efectivo',
            'notes'  => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $amount = $order->total;
        $token = Str::random(64);

        $payment = Payment::create([
            'user_id'           => $user->id,
            'order_id'          => $order->id,
            'method'            => 'efectivo',
            'amount'            => $amount,
            'status'            => 'pendiente',
            'reference'         => null,
            'transaction_token' => hash('sha256', $token),
            'notes'             => $request->notes,
            'paid_at'           => null,
        ]);

        return redirect()->route('payments.pending', $payment->id)
            ->with('success', 'Pago registrado. Pendiente de confirmacion por el administrador.');
    }

    /**
     * RUTA: PATCH /admin/payments/{payment}/approve
     * Confirma un pago en efectivo. SOLO administrador (verificado en
     * backend con hasRole, no solo ocultando el boton en Vue).
     */
    public function approve(Request $request, Payment $payment)
    {
        abort_unless(auth()->user()->hasRole('administrador'), 403);
        abort_if($payment->status === 'aprobado', 409, 'Este pago ya fue aprobado.');

        $payment->update([
            'status'  => 'aprobado',
            'paid_at' => now(),
        ]);

        $invoice = $this->generateInvoice($payment, $payment->user);
        $payment->user->notify(new PaymentConfirmedNotification($payment, $invoice));

        return back()->with('success', 'Pago confirmado y factura generada.');
    }

    public function invoice(Invoice $invoice)
    {
        abort_unless(
            auth()->id() === $invoice->user_id || auth()->user()->hasRole('administrador'),
            403
        );

        return Inertia::render('Payments/Invoice', [
            'invoice' => $invoice->load('payment'),
        ]);
    }

    public function pending(Payment $payment)
    {
        abort_unless(auth()->id() === $payment->user_id, 403);

        return Inertia::render('Payments/Pending', [
            'payment' => $payment,
        ]);
    }

    public function index()
    {
        return Inertia::render('Payments/Index', [
            'payments' => Payment::with(['user', 'invoice'])
                ->orderBy('created_at', 'desc')
                ->paginate(20),
        ]);
    }
}
