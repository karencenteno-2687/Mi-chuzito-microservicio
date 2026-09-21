<script setup>
/**
 * Orders/Show.vue — Detalle de un pedido puntual.
 *
 * Alimentado por OrderController::show(), que ya valida en el backend
 * que solo el cliente dueño, el repartidor/vendedor asignado o un
 * administrador puedan llegar a esta vista (403 en cualquier otro caso).
 * La sección "Seguimiento" es de solo lectura: se apaga/enciende según
 * los timestamps del pedido (created_at, picked_up_at, delivered_at),
 * sin ninguna acción que el cliente pueda ejecutar desde aquí.
 */
import { router } from '@inertiajs/vue3'

const { order } = defineProps(['order'])

const statusColors = {
    en_proceso: { bg: '#fff7ed', color: '#c2410c', label: 'En proceso' },
    en_camino:  { bg: '#eff6ff', color: '#1d4ed8', label: 'En camino' },
    entregado:  { bg: '#f0fdf4', color: '#15803d', label: 'Entregado' },
    cancelado:  { bg: '#fef2f2', color: '#dc2626', label: 'Cancelado' },
}

const formatPrice = (p) => '$' + Number(p).toLocaleString('es-CO')
const formatDate  = (d) => d ? new Date(d).toLocaleString('es-CO') : '-'
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px">
        <div style="display:flex;align-items:center;gap:10px;cursor:pointer" @click="router.visit('/')">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/orders" style="font-size:13px;color:#64748b;text-decoration:none">Mis pedidos</a>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:800px;margin:2rem auto;padding:0 1rem">

        <div style="display:flex;align-items:center;gap:12px;margin-bottom:1.5rem">
            <button @click="router.visit('/orders')" style="background:none;border:none;cursor:pointer;color:#64748b;display:flex;align-items:center;gap:4px;font-size:13px">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                Volver
            </button>
            <h1 style="font-size:20px;font-weight:700;color:#1e293b;margin:0">Pedido #{{ order.id }}</h1>
            <span :style="`font-size:12px;padding:4px 12px;border-radius:20px;font-weight:500;background:${statusColors[order.status]?.bg};color:${statusColors[order.status]?.color}`">
                {{ statusColors[order.status]?.label }}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">

            <!-- Info pedido -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:1rem">Informacion del pedido</h3>
                <div style="font-size:13px;color:#64748b;margin-bottom:6px">Fecha: {{ formatDate(order.created_at) }}</div>
                <div style="font-size:13px;color:#64748b;margin-bottom:6px">Direccion: {{ order.address }}</div>
                <div style="font-size:13px;color:#64748b;margin-bottom:6px">Pago: {{ order.payment_method }}</div>
                <div v-if="order.notes" style="font-size:13px;color:#64748b">Notas: {{ order.notes }}</div>
            </div>

            <!-- Tiempos -->
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem">
                <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:1rem">Seguimiento</h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div :style="`width:8px;height:8px;border-radius:50%;background:${order.created_at ? '#f97316' : '#e2e8f0'}`"></div>
                        <div>
                            <div style="font-size:12px;font-weight:500;color:#1e293b">Pedido recibido</div>
                            <div style="font-size:11px;color:#94a3b8">{{ formatDate(order.created_at) }}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div :style="`width:8px;height:8px;border-radius:50%;background:${order.picked_up_at ? '#3b82f6' : '#e2e8f0'}`"></div>
                        <div>
                            <div style="font-size:12px;font-weight:500;color:#1e293b">En camino</div>
                            <div style="font-size:11px;color:#94a3b8">{{ formatDate(order.picked_up_at) }}</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div :style="`width:8px;height:8px;border-radius:50%;background:${order.delivered_at ? '#15803d' : '#e2e8f0'}`"></div>
                        <div>
                            <div style="font-size:12px;font-weight:500;color:#1e293b">Entregado</div>
                            <div style="font-size:11px;color:#94a3b8">{{ formatDate(order.delivered_at) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1rem">
            <h3 style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:1rem">Productos</h3>
            <div v-for="item in order.items" :key="item.id" style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9">
                <img :src="item.image_url" :alt="item.name" style="width:48px;height:48px;border-radius:8px;object-fit:cover;flex-shrink:0"/>
                <div style="flex:1">
                    <div style="font-size:14px;font-weight:500;color:#1e293b">{{ item.name }}</div>
                    <div style="font-size:12px;color:#64748b">{{ formatPrice(item.price) }} x {{ item.qty }}</div>
                </div>
                <div style="font-size:14px;font-weight:600;color:#1e293b">{{ formatPrice(item.subtotal) }}</div>
            </div>

            <!-- Totales -->
            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid #e2e8f0">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:6px">
                    <span>Subtotal</span><span>{{ formatPrice(order.subtotal) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:6px">
                    <span>IVA (19%)</span><span>{{ formatPrice(order.tax) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:#1e293b;margin-top:8px;padding-top:8px;border-top:1px solid #e2e8f0">
                    <span>Total</span><span style="color:#f97316">{{ formatPrice(order.total) }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
</template>
