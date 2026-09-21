<script setup>
/**
 * Orders/AdminOrders.vue — Panel de administración de pedidos.
 *
 * Alimentado por OrderController::adminIndex(), que pagina (50 por
 * página, prop `orders` es un paginador de Laravel, por eso se accede
 * como orders.data) y envía también `repartidores` para poblar el
 * selector de reasignación manual.
 *
 * La pestaña "Activos" vs "Historial" es puramente de presentación
 * (se filtra en el propio componente sobre los datos ya traídos, no
 * hay una petición nueva al cambiar de pestaña). Un pedido sin
 * repartidor asignado muestra el selector + botón "Asignar", que llama
 * a OrderController::assignRepartidor (reasignación manual, distinta
 * de la asignación automática que ocurre en OrderController::store).
 */
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { orders, repartidores } = defineProps(['orders', 'repartidores'])

const statusColors = {
    en_proceso: { bg: '#fff7ed', color: '#c2410c', label: 'En proceso' },
    en_camino:  { bg: '#eff6ff', color: '#1d4ed8', label: 'En camino' },
    entregado:  { bg: '#f0fdf4', color: '#15803d', label: 'Entregado' },
    cancelado:  { bg: '#fef2f2', color: '#dc2626', label: 'Cancelado' },
}

const formatPrice = (p) => '$' + Number(p).toLocaleString('es-CO')
const formatDate  = (d) => d ? new Date(d).toLocaleString('es-CO') : '-'

const assignForm = useForm({ repartidor_id: '' })

const assign = (orderId) => {
    if (!assignForm.repartidor_id) return
    assignForm.patch(`/admin/orders/${orderId}/assign`)
}

const selectedTab = ref('activos')
// "Activos" = todo lo que aun no llego a un estado final; el resto
// (entregado/cancelado) pasa a "Historial". Se calcula una sola vez
// sobre orders.data porque la paginacion ya viene resuelta del backend.
const activos     = orders.data?.filter(o => o.status !== 'entregado' && o.status !== 'cancelado') ?? []
const completados = orders.data?.filter(o => o.status === 'entregado' || o.status === 'cancelado') ?? []
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100">
        <div style="display:flex;align-items:center;gap:10px;cursor:pointer" @click="router.visit('/dashboard')">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/admin/users" style="font-size:13px;color:#64748b;text-decoration:none">Usuarios</a>
            <a href="/admin/payments" style="font-size:13px;color:#64748b;text-decoration:none">Pagos</a>
            <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#f3e8ff;color:#7c3aed;font-weight:500">Administrador</span>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1100px;margin:2rem auto;padding:0 1rem">

        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Gestion de pedidos</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">Asigna repartidores y gestiona el estado de los pedidos</p>
        </div>

        <!-- Stats -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
            <div style="background:white;border-radius:10px;border:1px solid #e2e8f0;padding:1rem;text-align:center">
                <div style="font-size:24px;font-weight:800;color:#f97316">{{ activos.length }}</div>
                <div style="font-size:12px;color:#64748b">Activos</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #e2e8f0;padding:1rem;text-align:center">
                <div style="font-size:24px;font-weight:800;color:#1d4ed8">{{ activos.filter(o=>o.status==='en_camino').length }}</div>
                <div style="font-size:12px;color:#64748b">En camino</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #e2e8f0;padding:1rem;text-align:center">
                <div style="font-size:24px;font-weight:800;color:#15803d">{{ completados.filter(o=>o.status==='entregado').length }}</div>
                <div style="font-size:12px;color:#64748b">Entregados</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #e2e8f0;padding:1rem;text-align:center">
                <div style="font-size:24px;font-weight:800;color:#dc2626">{{ completados.filter(o=>o.status==='cancelado').length }}</div>
                <div style="font-size:12px;color:#64748b">Cancelados</div>
            </div>
        </div>

        <!-- Tabs -->
        <div style="display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:4px;margin-bottom:1.5rem;width:fit-content">
            <button @click="selectedTab='activos'" :style="`padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;${selectedTab==='activos' ? 'background:white;color:#f97316;box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'background:transparent;color:#94a3b8'}`">
                Activos ({{ activos.length }})
            </button>
            <button @click="selectedTab='historial'" :style="`padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;${selectedTab==='historial' ? 'background:white;color:#f97316;box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'background:transparent;color:#94a3b8'}`">
                Historial ({{ completados.length }})
            </button>
        </div>

        <!-- Lista pedidos activos -->
        <div v-if="selectedTab === 'activos'">
            <div v-if="activos.length === 0" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:3rem;text-align:center">
                <div style="font-size:15px;font-weight:600;color:#64748b">No hay pedidos activos</div>
            </div>

            <div v-for="order in activos" :key="order.id"
                style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1rem">

                <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1rem">
                    <div>
                        <div style="font-size:16px;font-weight:700;color:#1e293b">Pedido #{{ order.id }}</div>
                        <div style="font-size:12px;color:#94a3b8">{{ formatDate(order.created_at) }}</div>
                    </div>
                    <span :style="`font-size:12px;padding:4px 12px;border-radius:20px;font-weight:500;background:${statusColors[order.status]?.bg};color:${statusColors[order.status]?.color}`">
                        {{ statusColors[order.status]?.label }}
                    </span>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:1rem">
                    <div style="background:#f8fafc;border-radius:8px;padding:10px">
                        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:4px">Cliente</div>
                        <div style="font-size:13px;font-weight:500;color:#1e293b">{{ order.cliente?.name }}</div>
                        <div style="font-size:12px;color:#64748b">{{ order.cliente?.phone ?? '-' }}</div>
                    </div>
                    <div style="background:#eff6ff;border-radius:8px;padding:10px">
                        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:4px">Direccion</div>
                        <div style="font-size:13px;font-weight:500;color:#1e293b">{{ order.address }}</div>
                    </div>
                    <div style="background:#fff7ed;border-radius:8px;padding:10px">
                        <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:4px">Total</div>
                        <div style="font-size:16px;font-weight:800;color:#f97316">{{ formatPrice(order.total) }}</div>
                        <div style="font-size:12px;color:#64748b">{{ order.payment_method }}</div>
                    </div>
                </div>

                <!-- Productos -->
                <div style="background:#f8fafc;border-radius:8px;padding:10px;margin-bottom:1rem">
                    <div style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;margin-bottom:6px">Productos</div>
                    <div v-for="item in order.items" :key="item.id" style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;padding:2px 0">
                        <span>{{ item.name }} x{{ item.qty }}</span>
                        <span style="color:#1e293b;font-weight:500">{{ formatPrice(item.subtotal) }}</span>
                    </div>
                </div>

                <!-- Repartidor asignado o asignar -->
                <div style="display:flex;align-items:center;gap:10px">
                    <div v-if="order.repartidor" style="flex:1;background:#f0fdf4;border-radius:8px;padding:10px;display:flex;align-items:center;gap:8px">
                        <svg width="16" height="16" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        <div>
                            <div style="font-size:11px;color:#15803d;font-weight:600">Repartidor asignado</div>
                            <div style="font-size:13px;font-weight:500;color:#1e293b">{{ order.repartidor.name }}</div>
                        </div>
                    </div>
                    <template v-else>
                        <select v-model="assignForm.repartidor_id"
                            style="flex:1;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;background:white">
                            <option value="">Seleccionar repartidor</option>
                            <option v-for="rep in repartidores" :key="rep.id" :value="rep.id">{{ rep.name }}</option>
                        </select>
                        <button @click="assign(order.id)" :disabled="!assignForm.repartidor_id"
                            :style="`padding:10px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;${!assignForm.repartidor_id ? 'background:#e2e8f0;color:#94a3b8;cursor:not-allowed' : 'background:linear-gradient(135deg,#f97316,#ea580c);color:white'}`">
                            Asignar
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Historial -->
        <div v-if="selectedTab === 'historial'">
            <div v-for="order in completados" :key="order.id"
                style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.25rem;margin-bottom:0.75rem">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1e293b">Pedido #{{ order.id }} — {{ order.cliente?.name }}</div>
                        <div style="font-size:12px;color:#94a3b8">{{ order.address }}</div>
                        <div style="font-size:12px;color:#94a3b8">{{ formatDate(order.delivered_at ?? order.cancelled_at) }}</div>
                        <div v-if="order.repartidor" style="font-size:12px;color:#64748b;margin-top:2px">Repartidor: {{ order.repartidor.name }}</div>
                    </div>
                    <div style="text-align:right">
                        <span :style="`font-size:12px;padding:4px 12px;border-radius:20px;font-weight:500;background:${statusColors[order.status]?.bg};color:${statusColors[order.status]?.color}`">
                            {{ statusColors[order.status]?.label }}
                        </span>
                        <div style="font-size:16px;font-weight:700;color:#f97316;margin-top:4px">{{ formatPrice(order.total) }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</template>
