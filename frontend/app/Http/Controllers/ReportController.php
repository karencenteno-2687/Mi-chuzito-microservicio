<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Controlador del módulo de Reportes (solo para administradores).
 *
 * Cada método arma los datos para uno de los cuatro reportes del
 * negocio: ventas, inventario, pedidos y tiempos de entrega. Los
 * cálculos se hacen aquí, en PHP, y el frontend (los .vue de
 * Reports/) se limita a mostrarlos y a generar las descargas en
 * PDF/Excel a partir de esos mismos datos ya calculados.
 *
 * Los reportes que aceptan rango de fechas (ventas, pedidos, tiempos)
 * usan por defecto el mes en curso (desde el día 1 hasta hoy) cuando
 * el usuario no especifica desde/hasta.
 */
class ReportController extends Controller
{
    /**
     * Muestra el menú principal de reportes (las 4 tarjetas de acceso).
     *
     * @return \Inertia\Response Vista Reports/Index.
     */
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Reporte de Ventas: total vendido, total de pedidos y ranking de
     * productos más vendidos dentro de un rango de fechas.
     *
     * Importante: el total vendido y el ranking de productos solo
     * consideran pedidos con status = entregado (ventas confirmadas),
     * mientras que "totalPedidos" cuenta TODOS los pedidos del rango
     * sin importar su estado. Por eso ambos números pueden no coincidir
     * si hubo pedidos cancelados o aún en curso en el periodo.
     *
     * El ranking de productos se arma iterando la columna items (json)
     * de cada pedido entregado, en lugar de una relación a productos,
     * porque items guarda una copia histórica del producto al momento
     * de la compra (ver OrderController::store) y así el reporte no
     * se ve afectado si el producto cambió de nombre o precio después.
     *
     * @param Request $request Acepta 'desde' y 'hasta' (Y-m-d) por query string.
     * @return \Inertia\Response Vista Reports/Ventas.
     */
    public function ventas(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $ordersEntregados = Order::where('status', 'entregado')
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->get();

        $totalVentas  = $ordersEntregados->sum('total');
        $totalPedidos = Order::whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])->count();

        $productosMasVendidos = collect();

        // Se acumula manualmente en un Collection indexado por nombre
        // de producto, en vez de una consulta SQL agregada, porque los
        // productos viven dentro del array json 'items' de cada pedido
        // y no en una tabla relacional aparte.
        foreach ($ordersEntregados as $order) {
            foreach (($order->items ?? []) as $item) {
                $name     = $item['name'] ?? 'Producto';
                $qty      = (int) ($item['qty'] ?? 0);
                $subtotal = (float) ($item['subtotal'] ?? 0);

                $current = $productosMasVendidos->get($name, [
                    'name' => $name,
                    'total_vendido' => 0,
                    'total_ingresos' => 0,
                ]);

                $current['total_vendido']  += $qty;
                $current['total_ingresos'] += $subtotal;

                $productosMasVendidos->put($name, $current);
            }
        }

        $productosMasVendidos = $productosMasVendidos->values()->sortByDesc('total_vendido')->values();

        return Inertia::render('Reports/Ventas', [
            'totalVentas'          => $totalVentas,
            'totalPedidos'         => $totalPedidos,
            'productosMasVendidos' => $productosMasVendidos,
            'desde'                => $desde,
            'hasta'                => $hasta,
        ]);
    }

    /**
     * Reporte de Inventario: estado actual (sin rango de fechas) de
     * todos los productos, con conteo de disponibles vs. agotados.
     *
     * A diferencia de los otros tres reportes, este es una "foto" del
     * momento (no histórico): refleja el stock justo en el instante
     * en que se consulta.
     *
     * @return \Inertia\Response Vista Reports/Inventario.
     */
    public function inventario()
    {
        $productos = Product::select('name', 'category', 'price', 'stock', 'is_available')->get();

        $disponibles = $productos->where('is_available', true)->count();
        $agotados    = $productos->where('is_available', false)->count();

        return Inertia::render('Reports/Inventario', [
            'productos'   => $productos,
            'disponibles' => $disponibles,
            'agotados'    => $agotados,
        ]);
    }

    /**
     * Reporte de Pedidos: total de pedidos del periodo, desglose por
     * estado y el detalle completo de cada pedido.
     *
     * A diferencia del reporte de ventas, aquí SÍ se incluyen pedidos
     * en cualquier estado (en_proceso, en_camino, entregado, cancelado);
     * el objetivo es dar visibilidad operativa completa, no solo de
     * ventas cerradas.
     *
     * @param Request $request Acepta 'desde' y 'hasta' (Y-m-d) por query string.
     * @return \Inertia\Response Vista Reports/Pedidos.
     */
    public function pedidos(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $query = Order::with('cliente')
            ->whereBetween('created_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);

        // Se clona la query base tres veces (total, por estado, y el
        // listado final) porque cada get()/count() consume el query
        // builder; clonar evita repetir el where/whereBetween a mano.
        $totalPedidos = (clone $query)->count();

        $porEstado = (clone $query)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();

        $pedidos = $query->orderByDesc('created_at')->get()->map(function ($order) {
            return [
                'id'            => $order->id,
                'customer_name' => $order->cliente->name ?? 'N/A',
                'total'         => $order->total,
                'status'        => $order->status,
                'created_at'    => $order->created_at->format('Y-m-d H:i'),
            ];
        });

        return Inertia::render('Reports/Pedidos', [
            'totalPedidos' => $totalPedidos,
            'porEstado'    => $porEstado,
            'pedidos'      => $pedidos,
            'desde'        => $desde,
            'hasta'        => $hasta,
        ]);
    }

    /**
     * Reporte de Tiempos de Entrega: cuánto tarda cada pedido entre
     * que el repartidor lo recoge (picked_up_at) y lo entrega
     * (delivered_at), más el promedio del periodo.
     *
     * Solo se consideran pedidos que YA tienen ambos timestamps
     * llenos; un pedido cancelado o aún en camino no tiene
     * delivered_at y por lo tanto no aparece aquí. El filtro de fechas
     * se aplica sobre delivered_at (no sobre created_at, como en los
     * otros reportes) porque lo que interesa medir es cuándo se
     * completaron las entregas, no cuándo se generó el pedido.
     *
     * @param Request $request Acepta 'desde' y 'hasta' (Y-m-d) por query string.
     * @return \Inertia\Response Vista Reports/Tiempos.
     */
    public function tiempos(Request $request)
    {
        $desde = $request->get('desde', now()->startOfMonth()->toDateString());
        $hasta = $request->get('hasta', now()->toDateString());

        $orders = Order::with(['cliente', 'repartidor'])
            ->whereNotNull('picked_up_at')
            ->whereNotNull('delivered_at')
            ->whereBetween('delivered_at', [$desde . ' 00:00:00', $hasta . ' 23:59:59'])
            ->orderByDesc('delivered_at')
            ->get();

        $entregas = $orders->map(function ($order) {
            return [
                'id'              => $order->id,
                'customer_name'   => $order->cliente->name ?? 'N/A',
                'delivery_person' => $order->repartidor->name ?? 'N/A',
                'started_at'      => $order->picked_up_at->format('Y-m-d H:i'),
                'delivered_at'    => $order->delivered_at->format('Y-m-d H:i'),
                'minutos'         => $order->picked_up_at->diffInMinutes($order->delivered_at),
            ];
        });

        $promedioMinutos = $entregas->avg('minutos');

        return Inertia::render('Reports/Tiempos', [
            'entregas'        => $entregas,
            'promedioMinutos' => round($promedioMinutos ?? 0, 1),
            'desde'           => $desde,
            'hasta'           => $hasta,
        ]);
    }
}
