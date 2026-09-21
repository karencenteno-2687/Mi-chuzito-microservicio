<script setup>
/**
 * Orders/RepartidorOrders.vue — "Mis entregas" (vista del repartidor).
 *
 * Alimentado por OrderController::repartidorOrders(), que ya trae los
 * pedidos ordenados por relevancia operativa (en_camino primero).
 * El único botón de acción disponible depende de dos campos del
 * backend a la vez, no solo de `status`:
 *   - Si status=en_proceso y ready_at es null -> el vendedor aún no
 *     alistó el pedido (ver OrderController::markReady), se muestra
 *     el aviso "Esperando..." sin botón.
 *   - Si status=en_proceso y ready_at SÍ tiene valor -> aparece
 *     "Recoger pedido", que dispara updateStatus a en_camino.
 *   - Si status=en_camino -> aparece "Confirmar entrega", que dispara
 *     updateStatus a entregado.
 * Esto refleja en el frontend el mismo "puente" ready_at que existe
 * en el backend entre vendedor y repartidor.
 */
import { useForm, router } from '@inertiajs/vue3'

const { orders } = defineProps(['orders'])

const statusColors = {
    en_proceso: { bg: '#fff7ed', color: '#c2410c', label: 'En proceso' },
    en_camino:  { bg: '#eff6ff', color: '#1d4ed8', label: 'En camino' },
    entregado:  { bg: '#f0fdf4', color: '#15803d', label: 'Entregado' },
    cancelado:  { bg: '#fef2f2', color: '#dc2626', label: 'Cancelado' },
}

const formatPrice = (p) => '$' + Number(p).toLocaleString('es-CO')
const formatDate  = (d) => new Date(d).toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' })

// Llama a OrderController::updateStatus, que valida la transición
// contra Order::canTransitionTo antes de aplicarla.
const updateStatus = (orderId, status) => {
    useForm({ status }).patch(`/orders/${orderId}/status`)
}
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
            <a href="/perfil" style="font-size:13px;color:#64748b;text-decoration:none">Mi perfil</a>
            <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#f0fdf4;color:#15803d;font-weight:500">Repartidor</span>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1000px;margin:2rem auto;padding:0 1rem">
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Mis entregas</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">Pedidos asignados a ti y su estado</p>
        </div>

        <div v-if="orders.length === 0" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:3rem;text-align:center">
            <div style="font-size:16px;font-weight:600;color:#64748b">No tienes pedidos asignados por el momento</div>
        </div>

        <div v-for="order in orders" :key="order.id" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                <div>
                    <div style="font-size:15px;font-weight:600;color:#1e293b">Pedido #{{ order.id }}</div>
                    <div style="font-size:12px;color:#94a3b8">{{ formatDate(order.created_at) }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="order.status === 'en_proceso' && order.ready_at" style="font-size:11px;padding:2px 8px;border-radius:4px;background:#fefce8;color:#854d0e;font-weight:500">Listo para recoger</span>
                    <span :style="`font-size:12px;padding:4px 12px;border-radius:20px;font-weight:500;background:${statusColors[order.status]?.bg};color:${statusColors[order.status]?.color}`">
                        {{ statusColors[order.status]?.label }}
                    </span>
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-bottom:1rem">
                <div style="background:#f8fafc;border-radius:8px;padding:12px;flex:1">
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">CLIENTE</div>
                    <div style="font-size:13px;color:#1e293b">{{ order.cliente?.name }}</div>
                </div>
                <div style="background:#f8fafc;border-radius:8px;padding:12px;flex:1">
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">DIRECCION</div>
                    <div style="font-size:13px;color:#1e293b">{{ order.address }}</div>
                </div>
                <div style="background:#f8fafc;border-radius:8px;padding:12px;flex:1">
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">TOTAL</div>
                    <div style="font-size:15px;font-weight:700;color:#f97316">{{ formatPrice(order.total) }}</div>
                </div>
            </div>

            <div style="display:flex;gap:8px">
                <div v-if="order.status === 'en_proceso' && !order.ready_at" style="flex:1;padding:10px;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;border-radius:8px;font-size:13px;font-weight:600;text-align:center">
                    Esperando que el vendedor prepare el pedido
                </div>
                <button v-if="order.status === 'en_proceso' && order.ready_at" @click="updateStatus(order.id, 'en_camino')"
                    style="flex:1;padding:10px;background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                    Recoger pedido
                </button>
                <button v-if="order.status === 'en_camino'" @click="updateStatus(order.id, 'entregado')"
                    style="flex:1;padding:10px;background:linear-gradient(135deg,#22c55e,#15803d);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                    Confirmar entrega
                </button>
                <div v-if="order.status === 'entregado'" style="flex:1;padding:10px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;font-weight:600;text-align:center">
                    Entregado
                </div>
                <div v-if="order.status === 'cancelado'" style="flex:1;padding:10px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;font-size:13px;font-weight:600;text-align:center">
                    Cancelado
                </div>
            </div>
        </div>
    </div>
</div>
</template>
