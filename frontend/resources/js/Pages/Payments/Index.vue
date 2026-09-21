<script setup>
import { router } from '@inertiajs/vue3'

const { payments } = defineProps(['payments'])
const logout = () => router.post('/logout')

const approve = (id) => {
    if (confirm('Aprobar este pago y generar factura?')) {
        router.patch(`/admin/payments/${id}/approve`)
    }
}

const statusColor = (status) => {
    const colors = {
        aprobado:  { bg: '#f0fdf4', color: '#15803d' },
        pendiente: { bg: '#fff7ed', color: '#c2410c' },
        rechazado: { bg: '#fef2f2', color: '#dc2626' },
    }
    return colors[status] ?? { bg: '#f1f5f9', color: '#64748b' }
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
            <a href="/dashboard" style="font-size:13px;color:#64748b;text-decoration:none">Dashboard</a>
            <a href="/admin/users" style="font-size:13px;color:#64748b;text-decoration:none">Usuarios</a>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1100px;margin:2rem auto;padding:0 1rem">

        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Gestion de pagos</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">Revisa y aprueba los pagos pendientes</p>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0">
                <span style="font-size:14px;font-weight:600;color:#1e293b">Total: {{ payments.total }} pagos</span>
            </div>
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Cliente</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Metodo</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Monto</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Referencia</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Estado</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Factura</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Accion</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in payments.data" :key="p.id" style="border-top:1px solid #f1f5f9">
                        <td style="padding:14px 16px">
                            <div style="font-size:14px;font-weight:500;color:#1e293b">{{ p.user?.name }}</div>
                            <div style="font-size:12px;color:#94a3b8">{{ p.user?.email }}</div>
                        </td>
                        <td style="padding:14px 16px;font-size:13px;color:#64748b;text-transform:capitalize">{{ p.method }}</td>
                        <td style="padding:14px 16px;font-size:14px;font-weight:500;color:#1e293b">${{ Number(p.amount).toLocaleString('es-CO') }}</td>
                        <td style="padding:14px 16px;font-size:13px;color:#64748b">{{ p.reference ?? '-' }}</td>
                        <td style="padding:14px 16px">
                            <span :style="`font-size:12px;padding:3px 10px;border-radius:20px;font-weight:500;background:${statusColor(p.status).bg};color:${statusColor(p.status).color}`">
                                {{ p.status }}
                            </span>
                        </td>
                        <td style="padding:14px 16px">
                            <button v-if="p.invoice" @click="router.visit(`/payments/invoice/${p.invoice.id}`)"
                                style="font-size:12px;color:#f97316;background:none;border:none;cursor:pointer;font-weight:500;padding:0">
                                Ver factura
                            </button>
                            <span v-else style="font-size:12px;color:#94a3b8">Sin factura</span>
                        </td>
                        <td style="padding:14px 16px">
                            <button v-if="p.status === 'pendiente'" @click="approve(p.id)"
                                style="padding:5px 12px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                Aprobar
                            </button>
                            <span v-else style="font-size:12px;color:#94a3b8">-</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="padding:1rem 1.5rem;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:13px;color:#64748b">Pagina {{ payments.current_page }} de {{ payments.last_page }}</span>
                <div style="display:flex;gap:6px">
                    <button v-if="payments.prev_page_url" @click="router.visit(payments.prev_page_url)"
                        style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;background:white;font-size:13px;cursor:pointer">Anterior</button>
                    <button v-if="payments.next_page_url" @click="router.visit(payments.next_page_url)"
                        style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;background:white;font-size:13px;cursor:pointer">Siguiente</button>
                </div>
            </div>
        </div>
    </div>
</div>
</template>