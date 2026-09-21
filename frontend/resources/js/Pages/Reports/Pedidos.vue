<script setup>
/**
 * Reports/Pedidos.vue — Reporte de Pedidos.
 *
 * Recibe de ReportController::pedidos() el total del rango, el
 * desglose por estado (porEstado) y el detalle fila por fila. A
 * diferencia de Ventas, aquí SÍ se cuentan pedidos en cualquier
 * estado (no solo entregado), porque el objetivo es visibilidad
 * operativa completa del periodo.
 */
import { router } from '@inertiajs/vue3'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'
import * as XLSX from 'xlsx'

const props = defineProps({
    totalPedidos: Number,
    porEstado: Array,
    pedidos: Array,
    desde: String,
    hasta: String,
})

// Mapeos locales, deben mantenerse en sincronia con Order::STATUSES.
const estadoLabel = (status) => {
    const labels = { en_proceso: 'En proceso', en_camino: 'En camino', entregado: 'Entregado', cancelado: 'Cancelado' }
    return labels[status] || status
}
const estadoColor = (status) => {
    const colors = {
        en_proceso: 'background:#fff7ed;color:#c2410c',
        en_camino: 'background:#eff6ff;color:#1d4ed8',
        entregado: 'background:#f0fdf4;color:#15803d',
        cancelado: 'background:#fef2f2;color:#dc2626',
    }
    return colors[status] || 'background:#f1f5f9;color:#64748b'
}

const filtrar = () => {
    const desde = document.getElementById('desde').value
    const hasta = document.getElementById('hasta').value
    window.location.href = '/reports/pedidos?desde=' + desde + '&hasta=' + hasta
}

const descargarPDF = () => {
    const doc = new jsPDF()
    doc.setFontSize(18)
    doc.text('Reporte de Pedidos - Mi Chuzito', 14, 20)
    doc.setFontSize(11)
    doc.text('Periodo: ' + props.desde + ' al ' + props.hasta, 14, 30)
    doc.text('Total Pedidos: ' + props.totalPedidos, 14, 38)

    autoTable(doc, {
        startY: 47,
        head: [['#', 'Cliente', 'Total', 'Estado', 'Fecha']],
        body: props.pedidos.map(p => [p.id, p.customer_name, '$' + p.total, estadoLabel(p.status), p.created_at]),
    })

    doc.save('reporte-pedidos-' + props.desde + '-' + props.hasta + '.pdf')
}

const descargarExcel = () => {
    const data = props.pedidos.map(p => ({
        Numero: p.id,
        Cliente: p.customer_name,
        Total: p.total,
        Estado: estadoLabel(p.status),
        Fecha: p.created_at,
    }))
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(data), 'Pedidos')
    XLSX.writeFile(wb, 'reporte-pedidos-' + props.desde + '-' + props.hasta + '.xlsx')
}
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:Figtree,ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/reports" style="font-size:13px;color:#64748b;text-decoration:none">Volver a reportes</a>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:900px;margin:2rem auto;padding:0 1rem">
        <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0 0 1.5rem">Reporte de Pedidos</h1>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1rem 1.5rem;margin-bottom:1.5rem;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
            <div>
                <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">DESDE</label>
                <input type="date" id="desde" :value="desde" style="padding:8px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px"/>
            </div>
            <div>
                <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">HASTA</label>
                <input type="date" id="hasta" :value="hasta" style="padding:8px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px"/>
            </div>
            <button @click="filtrar" style="padding:9px 18px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Filtrar</button>
            <button @click="descargarPDF" style="padding:9px 18px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Descargar PDF</button>
            <button @click="descargarExcel" style="padding:9px 18px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Descargar Excel</button>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem">
            <div style="font-size:12px;color:#64748b">Total Pedidos en el periodo</div>
            <div style="font-size:26px;font-weight:700;color:#2563eb;margin-top:4px">{{ totalPedidos }}</div>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem">
            <div style="font-weight:700;color:#1e293b;font-size:15px;margin-bottom:1rem">Pedidos por estado</div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                <div v-for="estado in porEstado" :key="estado.status" style="flex:1;min-width:120px;text-align:center;padding:1rem;border-radius:8px;background:#f8fafc">
                    <div style="font-size:22px;font-weight:700;color:#1e293b">{{ estado.total }}</div>
                    <span :style="estadoColor(estado.status) + ';padding:2px 10px;border-radius:20px;font-size:11px;display:inline-block;margin-top:6px'">{{ estadoLabel(estado.status) }}</span>
                </div>
            </div>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;font-weight:700;color:#1e293b;font-size:15px">Detalle de pedidos</div>
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">#</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Cliente</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Total</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="pedido in pedidos" :key="pedido.id" style="border-bottom:1px solid #e2e8f0">
                        <td style="padding:10px 16px;color:#64748b">{{ pedido.id }}</td>
                        <td style="padding:10px 16px;font-weight:600;color:#1e293b">{{ pedido.customer_name }}</td>
                        <td style="padding:10px 16px;color:#64748b">${{ pedido.total }}</td>
                        <td style="padding:10px 16px">
                            <span :style="estadoColor(pedido.status) + ';padding:2px 10px;border-radius:20px;font-size:11px'">{{ estadoLabel(pedido.status) }}</span>
                        </td>
                        <td style="padding:10px 16px;color:#64748b">{{ pedido.created_at }}</td>
                    </tr>
                    <tr v-if="pedidos.length === 0">
                        <td colspan="5" style="padding:2rem;text-align:center;color:#94a3b8">No hay pedidos en este periodo</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>
