<script setup>
/**
 * Orders/Index.vue — Historial de pedidos del cliente.
 *
 * Recibe la lista `orders` ya filtrada por usuario desde
 * OrderController::index() (ordenada del más reciente al más antiguo).
 * Es de solo lectura: cada tarjeta lleva al detalle (Orders/Show.vue);
 * la creación de pedidos ocurre en el menú público, no aquí.
 */
import { router, usePage } from '@inertiajs/vue3'

const { orders } = defineProps(['orders'])
const { auth } = usePage().props
const logout = () => router.post('/logout')

// Debe reflejar exactamente los mismos 4 estados y transiciones
// definidos en Order::STATUSES / canTransitionTo (backend).
const statusColors = {
    en_proceso: { bg: '#fff7ed', color: '#c2410c', label: 'En proceso' },
    en_camino:  { bg: '#eff6ff', color: '#1d4ed8', label: 'En camino' },
    entregado:  { bg: '#f0fdf4', color: '#15803d', label: 'Entregado' },
    cancelado:  { bg: '#fef2f2', color: '#dc2626', label: 'Cancelado' },
}

const formatPrice = (p) => '$' + Number(p).toLocaleString('es-CO')
const formatDate  = (d) => new Date(d).toLocaleDateString('es-CO', { day:'2-digit', month:'short', year:'numeric' })
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
            <a href="/" style="font-size:13px;color:#64748b;text-decoration:none">Menu</a>
            <a href="/perfil" style="font-size:13px;color:#64748b;text-decoration:none">Mi perfil</a>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:900px;margin:2rem auto;padding:0 1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <div>
                <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Mis pedidos</h1>
                <p style="font-size:13px;color:#64748b;margin:4px 0 0">Historial de todos tus pedidos</p>
            </div>
            <button @click="router.visit('/')"
                style="padding:10px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                + Nuevo pedido
            </button>
        </div>

        <!-- Sin pedidos -->
        <div v-if="orders.length === 0" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:3rem;text-align:center">
            <svg width="56" height="56" fill="none" stroke="#e2e8f0" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            <div style="font-size:16px;font-weight:600;color:#64748b;margin-bottom:6px">No tienes pedidos aun</div>
            <div style="font-size:13px;color:#94a3b8">Explora nuestro menu y realiza tu primer pedido</div>
        </div>

        <!-- Lista de pedidos -->
        <div v-for="order in orders" :key="order.id"
            style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1rem;cursor:pointer"
            @click="router.visit(`/orders/${order.id}`)"
            @mouseover="$event.currentTarget.style.borderColor='#f97316'"
            @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                <div>
                    <div style="font-size:15px;font-weight:600;color:#1e293b">Pedido #{{ order.id }}</div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:2px">{{ formatDate(order.created_at) }}</div>
                </div>
                <span :style="`font-size:12px;padding:4px 12px;border-radius:20px;font-weight:500;background:${statusColors[order.status]?.bg};color:${statusColors[order.status]?.color}`">
                    {{ statusColors[order.status]?.label }}
                </span>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:1rem">
                <div v-for="item in order.items.slice(0,3)" :key="item.id"
                    style="display:flex;align-items:center;gap:6px;background:#f8fafc;border-radius:8px;padding:6px 10px">
                    <img :src="item.image_url" :alt="item.name" style="width:28px;height:28px;border-radius:4px;object-fit:cover"/>
                    <span style="font-size:12px;color:#1e293b">{{ item.name }} x{{ item.qty }}</span>
                </div>
                <div v-if="order.items.length > 3"
                    style="display:flex;align-items:center;background:#f1f5f9;border-radius:8px;padding:6px 10px">
                    <span style="font-size:12px;color:#64748b">+{{ order.items.length - 3 }} mas</span>
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid #f1f5f9;padding-top:12px">
                <span style="font-size:13px;color:#64748b">{{ order.items.length }} productos</span>
                <span style="font-size:16px;font-weight:700;color:#f97316">{{ formatPrice(order.total) }}</span>
            </div>
        </div>
    </div>
</div>
</template>
