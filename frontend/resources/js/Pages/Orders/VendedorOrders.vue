<script setup>
/**
 * Orders/VendedorOrders.vue — Gestion de pedidos (vista del vendedor).
 *
 * Alimentado por OrderController::vendedorOrders(), que combina dos
 * grupos en una sola lista: los pedidos que este vendedor ya tomó
 * (vendedor_id = él) y los pedidos "libres" sin vendedor asignado
 * (marcados aquí como "Disponible"). Las tres acciones posibles se
 * conectan cada una con un endpoint distinto de OrderController:
 *   - claimOrder  -> POST /orders/{id}/claim   (reclamar un pedido libre)
 *   - markReady   -> POST /orders/{id}/ready   (avisar al repartidor)
 *   - updateStatus -> PATCH /orders/{id}/status (aquí solo para cancelar)
 * Un pedido ya tomado por el vendedor no puede volver a "Tomar pedido";
 * el bloque v-else-if solo muestra las acciones que correspondan según
 * status y ready_at, igual que en RepartidorOrders.vue.
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

// El backend re-valida que el pedido siga libre (status=en_proceso y
// sin vendedor_id) antes de asignarlo; esto es solo el disparador.
const claimOrder = (orderId) => {
    useForm({}).post(`/orders/${orderId}/claim`)
}

const updateStatus = (orderId, status) => {
    useForm({ status }).patch(`/orders/${orderId}/status`)
}

// Avisa al repartidor (OrderReadyNotification) que el pedido ya está
// físicamente preparado y puede recogerse.
const markReady = (orderId) => {
    useForm({}).post(`/orders/${orderId}/ready`)
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
            <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#fff7ed;color:#f97316;font-weight:500">Vendedor</span>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1000px;margin:2rem auto;padding:0 1rem">
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Gestion de pedidos</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">Pedidos disponibles y asignados a ti</p>
        </div>

        <div v-if="orders.length === 0" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:3rem;text-align:center">
            <div style="font-size:16px;font-weight:600;color:#64748b">No hay pedidos disponibles por el momento</div>
        </div>

        <div v-for="order in orders" :key="order.id" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1rem">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                <div>
                    <div style="font-size:15px;font-weight:600;color:#1e293b">Pedido #{{ order.id }}</div>
                    <div style="font-size:12px;color:#94a3b8">{{ formatDate(order.created_at) }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px">
                    <span v-if="!order.vendedor_id" style="font-size:11px;padding:2px 8px;border-radius:4px;background:#fef9c3;color:#854d0e;font-weight:500">Disponible</span>
                    <span v-else style="font-size:11px;padding:2px 8px;border-radius:4px;background:#f0fdf4;color:#15803d;font-weight:500">Asignado a ti</span>
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
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">TOTAL</div>
                    <div style="font-size:15px;font-weight:700;color:#f97316">{{ formatPrice(order.total) }}</div>
                </div>
                <div style="background:#f8fafc;border-radius:8px;padding:12px;flex:1">
                    <div style="font-size:12px;font-weight:600;color:#64748b;margin-bottom:4px">PRODUCTOS</div>
                    <div style="font-size:13px;color:#1e293b">{{ order.items?.length }} items</div>
                </div>
            </div>

            <!-- Acciones RF-034, RF-035 -->
            <div style="display:flex;gap:8px">
                <button v-if="!order.vendedor_id" @click="claimOrder(order.id)"
                    style="flex:1;padding:10px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                    Tomar pedido
                </button>
                <template v-else-if="order.vendedor_id">
                    <button v-if="order.status === 'en_proceso' && !order.ready_at" @click="markReady(order.id)"
                        style="flex:1;padding:10px;background:linear-gradient(135deg,#eab308,#ca8a04);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                        Marcar listo para entrega
                    </button>
                    <div v-if="order.status === 'en_proceso' && order.ready_at" style="flex:1;padding:10px;background:#fefce8;color:#854d0e;border:1px solid #fde68a;border-radius:8px;font-size:13px;font-weight:600;text-align:center">
                        Listo, esperando repartidor
                    </div>
                    <button v-if="order.status === 'en_proceso'" @click="updateStatus(order.id, 'cancelado')"
                        style="padding:10px 16px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer">
                        Cancelar
                    </button>
                    <div v-if="order.status === 'en_camino'" style="flex:1;padding:10px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:8px;font-size:13px;font-weight:600;text-align:center">
                        En camino con el repartidor
                    </div>
                    <div v-if="order.status === 'entregado'" style="flex:1;padding:10px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;font-weight:600;text-align:center">
                        Entregado
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
</template>
