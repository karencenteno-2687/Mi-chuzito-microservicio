<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;

abstract class Controller
{
    /**
     * MÉTODO COMPARTIDO — usado por PaymentController y PayPalController
     * QUÉ HACE:        genera la factura de un pago, tomando subtotal/IVA/total
     *                   DIRECTAMENTE del pedido asociado (Order), en vez de
     *                   recalcular un IVA nuevo sobre el monto del pago.
     * POR QUÉ:         $payment->amount ya es igual a $order->total, que ya
     *                   incluye IVA. Volver a calcular 19% sobre eso duplicaría
     *                   el impuesto en la factura.
     */
    protected function generateInvoice(Payment $payment, $user): Invoice
    {
        $order = $payment->order; // gracias a la relación que ya creamos

        if ($order) {
            // Caso normal: el pago viene de un pedido real, usamos SUS montos.
            $items    = $order->items;
            $subtotal = $order->subtotal;
            $tax      = $order->tax;
            $total    = $order->total;
        } else {
            // Respaldo por si algún día existe un pago sin pedido asociado.
            $items = [[
                'descripcion'    => 'Pago ' . $payment->method,
                'cantidad'       => 1,
                'valor_unitario' => $payment->amount,
                'subtotal'       => $payment->amount,
            ]];
            $subtotal = $payment->amount;
            $tax      = round($subtotal * 0.19, 2);
            $total    = $subtotal + $tax;
        }

        return Invoice::create([
            'payment_id'      => $payment->id,
            'user_id'         => $user->id,
            'invoice_number'  => Invoice::generateInvoiceNumber(),
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'total'           => $total,
            'status'          => 'activa',
            'items'           => $items,
            'client_name'     => $user->name,
            'client_email'    => $user->email,
            'client_phone'    => $user->phone,
            'client_document' => $user->profile?->document_number,
            'issued_at'       => now(),
        ]);
    }
}