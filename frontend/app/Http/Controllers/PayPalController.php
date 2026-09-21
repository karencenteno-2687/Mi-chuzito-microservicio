<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Notifications\PaymentConfirmedNotification;

class PayPalController extends Controller
{
    private function baseUrl(): string
    {
        return config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function accessToken(): string
    {
        $response = Http::asForm()
            ->withBasicAuth(config('services.paypal.client_id'), config('services.paypal.secret'))
            ->post($this->baseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        $response->throw();

        return $response->json('access_token');
    }

    public function checkout(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_if($order->payment, 409, 'Este pedido ya fue pagado.');

        $token = $this->accessToken();

        $usdAmount = round($order->total / 4000, 2);

        $response = Http::withToken($token)
            ->post($this->baseUrl() . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $order->id,
                    'amount' => [
                        'currency_code' => 'USD',
                        'value'         => number_format($usdAmount, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'brand_name'  => 'Mi Chuzito',
                    'user_action' => 'PAY_NOW',
                    'return_url'  => route('paypal.success', $order),
                    'cancel_url'  => route('paypal.cancel', $order),
                ],
            ]);

        $response->throw();
        $data = $response->json();

        $approveUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'] ?? null;
        abort_unless($approveUrl, 500, 'PayPal no devolvio un enlace de aprobacion.');

        return redirect()->away($approveUrl);
    }

    /**
     * CAMBIO: se agrega verificacion de idempotencia. Si el pedido ya
     * tiene un pago (por ejemplo, si PayPal reenvia la confirmacion o
     * el usuario recarga esta pagina), NO se vuelve a crear otro pago;
     * simplemente se le muestra la factura ya generada.
     */
    public function success(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->payment) {
            return redirect()->route('payments.invoice', $order->payment->invoice->id)
                ->with('success', 'Este pedido ya estaba pagado.');
        }

        $paypalOrderId = $request->query('token');
        abort_unless($paypalOrderId, 400, 'Falta el token de PayPal.');

        $token = $this->accessToken();

        // CAMBIO: PayPal exige un objeto JSON vacio "{}" en el cuerpo de
        // esta peticion. Sin un segundo argumento explicito, Laravel
        // enviaba un array vacio "[]", que PayPal rechazaba con
        // INVALID_REQUEST por no cumplir su schema. (object) [] fuerza
        // la serializacion correcta a "{}".
        $capture = Http::withToken($token)
            ->post($this->baseUrl() . "/v2/checkout/orders/{$paypalOrderId}/capture", (object) []);

        $capture->throw();
        $result = $capture->json();

        if (($result['status'] ?? null) !== 'COMPLETED') {
            return redirect()->route('payments.show', $order)
                ->withErrors(['paypal' => 'El pago no pudo completarse. Intenta de nuevo.']);
        }

        $payment = Payment::create([
            'user_id'           => $order->user_id,
            'order_id'          => $order->id,
            'method'            => 'paypal',
            'amount'            => $order->total,
            'status'            => 'aprobado',
            'reference'         => $paypalOrderId,
            'transaction_token' => hash('sha256', $paypalOrderId . Str::random(32)),
            'notes'             => 'Pago procesado via PayPal Sandbox',
            'paid_at'           => now(),
        ]);

        $invoice = $this->generateInvoice($payment, $request->user());

        $request->user()->notify(new PaymentConfirmedNotification($payment, $invoice));

        return redirect()->route('payments.invoice', $invoice->id)
            ->with('success', 'Pago con PayPal aprobado. Factura generada.');
    }

    public function cancel(Request $request, Order $order)
    {
        return redirect()->route('payments.show', $order)
            ->with('info', 'Cancelaste el pago con PayPal. Puedes intentarlo de nuevo o elegir efectivo.');
    }
}
