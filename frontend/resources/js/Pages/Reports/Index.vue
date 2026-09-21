<script setup>
/**
 * Reports/Index.vue — Menu principal de reportes (solo administrador).
 *
 * Es puramente de navegación: cada tarjeta enlaza a uno de los 4
 * reportes calculados en ReportController (ventas, inventario,
 * pedidos, tiempos). No recibe props porque no muestra datos propios.
 */
import { router } from '@inertiajs/vue3'
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/admin/orders" style="font-size:13px;color:#64748b;text-decoration:none">Panel admin</a>
            <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#fff7ed;color:#f97316;font-weight:500">Administrador</span>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:900px;margin:2rem auto;padding:0 1rem">
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Reportes</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">Consulta el desempeno del negocio</p>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <a href="/reports/ventas" style="text-decoration:none;background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;display:block">
                <div style="font-size:28px;margin-bottom:10px"></div>
                <div style="font-size:16px;font-weight:700;color:#1e293b">Reporte de Ventas</div>
                <div style="font-size:13px;color:#64748b;margin-top:4px">Total vendido, pedidos y productos mas vendidos por periodo.</div>
            </a>
            <a href="/reports/inventario" style="text-decoration:none;background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;display:block">
                <div style="font-size:28px;margin-bottom:10px"></div>
                <div style="font-size:16px;font-weight:700;color:#1e293b">Reporte de Inventario</div>
                <div style="font-size:13px;color:#64748b;margin-top:4px">Productos disponibles, agotados y niveles de stock.</div>
            </a>
            <a href="/reports/pedidos" style="text-decoration:none;background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;display:block">
                <div style="font-size:28px;margin-bottom:10px"></div>
                <div style="font-size:16px;font-weight:700;color:#1e293b">Reporte de Pedidos</div>
                <div style="font-size:13px;color:#64748b;margin-top:4px">Total de pedidos procesados por periodo y estado.</div>
            </a>
            <a href="/reports/tiempos" style="text-decoration:none;background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;display:block">
                <div style="font-size:28px;margin-bottom:10px"></div>
                <div style="font-size:16px;font-weight:700;color:#1e293b">Reporte de Tiempos de Entrega</div>
                <div style="font-size:13px;color:#64748b;margin-top:4px">Tiempo de entrega promedio por pedido.</div>
            </a>
        </div>
    </div>
</div>
</template>
