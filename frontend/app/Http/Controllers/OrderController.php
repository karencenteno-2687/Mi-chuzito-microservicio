<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Notifications\OrderReadyNotification;

/**
 * Controlador del módulo de Entregas.
 *
 * Maneja todo el ciclo de vida de un pedido: creación por el cliente,
 * asignación de repartidor, cambios de estado y las tres vistas
 * distintas del mismo listado de pedidos según el rol que consulte
 * (cliente, repartidor, vendedor o administrador).
 *
 * Flujo general de estados de un pedido (ver también Order::STATUSES
 * y Order::canTransitionTo):
 *
 *   en_proceso -> en_camino -> entregado
 *              -> cancelado
 *
 * Dentro de "en_proceso" existe además un sub-paso interno marcado
 * por el campo ready_at: un vendedor debe "alistar" el pedido
 * (markReady) antes de que el repartidor pueda recogerlo.
 */
class OrderController extends Controller
{
    /**
     * Crea un nuevo pedido a partir del carrito del cliente.
     *
     * Reglas de negocio aplicadas aquí:
     * - Se valida stock disponible producto por producto; si alguno
     *   no alcanza, se aborta todo el pedido (no hay creación parcial).
     * - El IVA se calcula fijo al 19% sobre el subtotal.
     * - Como el sistema solo maneja un repartidor activo, se le asigna
     *   automáticamente el pedido apenas se crea (no hay cola de
     *   asignación en esta etapa).
     * - Se descuenta el stock de cada producto comprado y, si algún
     *   producto llega a 0 unidades, se desactiva automáticamente
     *   (is_available = false) para que deje de aparecer en el menú
     *   público (ver MenuController::index).
     * - Las notificaciones al repartidor son "best effort": si fallan
     *   (por ejemplo, sin email configurado) no interrumpen la compra.
     *
     * @param Request $request Debe incluir items (array de {id, qty, note}),
     *        address, payment_method y opcionalmente notes.
     * @return \Illuminate\Http\RedirectResponse Redirige al detalle del
     *         pedido recién creado, o vuelve atrás con errores si no
     *         hay stock suficiente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:products,id',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.note'   => 'nullable|string|max:300',
            'address'        => 'required|string|max:255|min:10',
            'payment_method' => 'required|in:efectivo,transferencia',
            'notes'          => 'nullable|string|max:500',
        ], [
            'address.required' => 'La direccion de entrega es obligatoria.',
            'address.min'      => 'La direccion debe tener al menos 10 caracteres.',
            'items.required'   => 'Debes agregar al menos un producto.',
        ]);

        $user     = $request->user();
        $items    = [];
        $subtotal = 0;

        // Se valida stock y se arma el snapshot de cada item ANTES de
        // crear el pedido. Guardar precio/nombre/imagen en el propio
        // pedido (columna items, tipo json) evita que cambios futuros
        // en el catálogo (precio, nombre) alteren pedidos ya realizados.
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['id']);

            if (!$product->is_available || $product->stock < $item['qty']) {
                return back()->withErrors(['items' => "El producto {$product->name} no tiene suficiente stock."]);
            }

            $itemSubtotal = $product->price * $item['qty'];
            $subtotal    += $itemSubtotal;

            $items[] = [
                'id'        => $product->id,
                'name'      => $product->name,
                'price'     => $product->price,
                'qty'       => $item['qty'],
                'subtotal'  => $itemSubtotal,
                'image_url' => $product->image_url_full,
                'note'      => $item['note'] ?? '',
            ];
        }

        $tax   = round($subtotal * 0.19, 2);
        $total = $subtotal + $tax;

        // Como solo existe un repartidor en el sistema, se asigna automaticamente
        $repartidor = User::role('repartidor')->where('is_active', true)->first();

        $order = Order::create([
            'user_id'        => $user->id,
            'repartidor_id'  => $repartidor?->id,
            'assigned_at'    => $repartidor ? now() : null,
            'status'         => 'en_proceso',
            'items'          => $items,
            'subtotal'       => $subtotal,
            'tax'            => $tax,
            'total'          => $total,
            'address'        => $request->address,
            'notes'          => $request->notes,
            'payment_method' => $request->payment_method,
        ]);

        // Descuento de stock post-creación: se hace en un segundo loop
        // (no en el primero) para no tocar la base de datos si el
        // pedido termina fallando por falta de stock en algún item.
        foreach ($request->items as $item) {
            Product::where('id', $item['id'])->decrement('stock', $item['qty']);
            Product::where('id', $item['id'])->where('stock', 0)->update(['is_available' => false]);
        }

        activity()->causedBy($user)->performedOn($order)->log('order_created');

        // Notificar al unico repartidor del nuevo pedido
        if ($repartidor && $repartidor->email) {
            try {
                $repartidor->notify(new OrderStatusNotification($order));
            } catch (\Exception $e) {}
        }

        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Pedido creado correctamente.');
    }

    /**
     * Lista el historial de pedidos del cliente autenticado.
     *
     * @param Request $request
     * @return \Inertia\Response Vista Orders/Index con los pedidos del
     *         usuario, del más reciente al más antiguo.
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Muestra el detalle de un pedido puntual.
     *
     * Solo pueden verlo: el cliente dueño del pedido, el repartidor
     * asignado, el vendedor asignado, o cualquier administrador.
     * Cualquier otro usuario recibe un 403.
     *
     * @param Order $order
     * @return \Inertia\Response Vista Orders/Show con el pedido y sus
     *         relaciones (cliente, repartidor, vendedor) cargadas.
     */
    public function show(Order $order)
    {
        $user = auth()->user();

        abort_unless(
            $order->user_id === $user->id ||
            $order->repartidor_id === $user->id ||
            $order->vendedor_id === $user->id ||
            $user->hasRole('administrador'),
            403
        );

        return Inertia::render('Orders/Show', [
            'order' => $order->load('cliente', 'repartidor', 'vendedor'),
        ]);
    }

    /**
     * Cambia el estado de un pedido (usado por repartidor, vendedor o admin).
     *
     * La transición se valida contra Order::canTransitionTo para evitar
     * saltos inválidos (por ejemplo, pasar directo de "en_proceso" a
     * "entregado"). Según el nuevo estado, se registra el timestamp
     * correspondiente (picked_up_at, delivered_at o cancelled_at) y,
     * si aplica, se notifica al cliente.
     *
     * @param Request $request Debe incluir status (uno de los valores
     *        de Order::STATUSES).
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:en_proceso,en_camino,entregado,cancelado',
        ]);

        $user = auth()->user();

        abort_unless(
            $user->hasRole('administrador') ||
            $order->repartidor_id === $user->id ||
            $order->vendedor_id === $user->id,
            403
        );

        if (!$order->canTransitionTo($request->status)) {
            return back()->withErrors(['status' => 'Transicion de estado no permitida.']);
        }

        $timestamps = [
            'en_camino' => ['picked_up_at' => now()],
            'entregado' => ['delivered_at' => now()],
            'cancelado' => ['cancelled_at' => now()],
        ];

        $order->update(array_merge(
            ['status' => $request->status],
            $timestamps[$request->status] ?? []
        ));

        activity()
            ->causedBy($user)
            ->performedOn($order)
            ->withProperties(['new_status' => $request->status])
            ->log('order_status_updated');

        // Al cliente solo se le notifica en los cambios de estado que
        // le interesan como comprador; "en_proceso" no genera aviso.
        if (in_array($request->status, ['en_camino', 'entregado', 'cancelado'])) {
            try {
                $order->cliente->notify(new OrderStatusNotification($order));
            } catch (\Exception $e) {}
        }

        return back()->with('success', 'Estado actualizado correctamente.');
    }

    /**
     * Lista los pedidos asignados al repartidor autenticado.
     *
     * Se ordenan primero por relevancia operativa (en_camino antes que
     * en_proceso, y estos antes que los ya cerrados) y luego por fecha,
     * para que el repartidor vea de inmediato lo que tiene pendiente
     * de recoger o entregar.
     *
     * @param Request $request
     * @return \Inertia\Response Vista Orders/RepartidorOrders.
     */
    public function repartidorOrders(Request $request)
    {
        $orders = Order::where('repartidor_id', $request->user()->id)
            ->with('cliente')
            ->orderByRaw("FIELD(status, 'en_camino', 'en_proceso', 'entregado', 'cancelado')")
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/RepartidorOrders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Lista los pedidos relevantes para el vendedor autenticado.
     *
     * Incluye dos grupos: los pedidos que el vendedor ya tomó
     * (vendedor_id = él) y los pedidos "libres" que cualquier vendedor
     * puede reclamar (sin vendedor_id, en estado en_proceso). Así, el
     * vendedor ve en una sola pantalla tanto su trabajo en curso como
     * las oportunidades disponibles para tomar.
     *
     * @param Request $request
     * @return \Inertia\Response Vista Orders/VendedorOrders.
     */
    public function vendedorOrders(Request $request)
    {
        $user = $request->user();

        $orders = Order::where(function($q) use ($user) {
                $q->where('vendedor_id', $user->id)
                  ->orWhere(function($q2) {
                      $q2->whereNull('vendedor_id')->where('status', 'en_proceso');
                  });
            })
            ->with('cliente')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/VendedorOrders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Permite a un vendedor reclamar ("tomar") un pedido libre.
     *
     * Solo puede reclamarse un pedido que esté en_proceso y que aún
     * no tenga vendedor asignado; esto evita que dos vendedores tomen
     * el mismo pedido al mismo tiempo (condición de carrera simple,
     * validada a nivel de aplicación).
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function claimOrder(Request $request, Order $order)
    {
        abort_unless(auth()->user()->hasRole('vendedor'), 403);

        if ($order->status !== 'en_proceso' || $order->vendedor_id !== null) {
            return back()->withErrors(['order' => 'Este pedido no esta disponible.']);
        }

        $order->update(['vendedor_id' => auth()->id()]);
        activity()->causedBy(auth()->user())->performedOn($order)->log('order_claimed_by_vendor');

        return back()->with('success', 'Pedido tomado correctamente.');
    }

    /**
     * Marca un pedido como listo para ser recogido por el repartidor.
     *
     * Este paso lo ejecuta el vendedor (o un administrador) una vez
     * que ya preparó físicamente el pedido. Es el "puente" entre el
     * estado en_proceso y la posibilidad de que el repartidor lo
     * recoja (updateStatus a en_camino): mientras ready_at sea null,
     * el repartidor ve el pedido como "esperando al vendedor"
     * (ver Orders/RepartidorOrders.vue).
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markReady(Request $request, Order $order)
    {
        $user = auth()->user();

        abort_unless(
            $user->hasRole('administrador') || $order->vendedor_id === $user->id,
            403
        );

        if ($order->status !== 'en_proceso') {
            return back()->withErrors(['order' => 'Solo se puede marcar como listo un pedido en proceso.']);
        }

        if ($order->ready_at) {
            return back()->withErrors(['order' => 'Este pedido ya fue marcado como listo.']);
        }

        $order->update(['ready_at' => now()]);

        activity()->causedBy($user)->performedOn($order)->log('order_marked_ready');

        if ($order->repartidor && $order->repartidor->email) {
            try {
                $order->repartidor->notify(new \App\Notifications\OrderReadyNotification($order));
            } catch (\Exception $e) {}
        }

        return back()->with('success', 'Pedido marcado como listo. Se notificó al repartidor.');
    }

    /**
     * Panel del administrador: lista todos los pedidos del sistema.
     *
     * Se pagina (50 por página) porque, a diferencia de las demás
     * vistas de este controlador, aquí no hay filtro por usuario:
     * el administrador ve el total histórico de pedidos. También se
     * envía la lista de repartidores activos, para poblar el selector
     * de asignación manual en el frontend (ver assignRepartidor).
     *
     * @return \Inertia\Response Vista Orders/AdminOrders.
     */    public function adminIndex()
    {
        $orders = Order::with(['cliente', 'repartidor', 'vendedor'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $repartidores = User::role('repartidor')->where('is_active', true)->get(['id', 'name', 'phone']);

        return Inertia::render('Orders/AdminOrders', [
            'orders'       => $orders,
            'repartidores' => $repartidores,
        ]);
    }

    /**
     * Reasigna manualmente el repartidor de un pedido.
     *
     * Complementa la asignación automática de store(): si el
     * repartidor original no puede continuar, el administrador puede
     * reasignar el pedido a otro repartidor activo desde el panel.
     *
     * @param Request $request Debe incluir repartidor_id (debe existir
     *        en la tabla users).
     * @param Order $order
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignRepartidor(Request $request, Order $order)
    {
        abort_unless(auth()->user()->hasRole('administrador'), 403);

        $request->validate([
            'repartidor_id' => 'required|exists:users,id',
        ]);

        $order->update([
            'repartidor_id' => $request->repartidor_id,
            'assigned_at'   => now(),
        ]);

        $repartidor = User::find($request->repartidor_id);
        if ($repartidor && $repartidor->email) {
            try {
                $repartidor->notify(new OrderStatusNotification($order));
            } catch (\Exception $e) {}
        }

        activity()->causedBy(auth()->user())->performedOn($order)
            ->withProperties(['repartidor_id' => $request->repartidor_id])
            ->log('repartidor_assigned');

        return back()->with('success', 'Repartidor asignado correctamente.');
    }
}
