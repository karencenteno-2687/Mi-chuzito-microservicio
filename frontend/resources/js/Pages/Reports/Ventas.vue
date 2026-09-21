<script setup>
/**
 * Reports/Ventas.vue — Reporte de Ventas.
 *
 * Recibe de ReportController::ventas() los totales ya calculados en
 * el backend (totalVentas y productosMasVendidos solo consideran
 * pedidos entregado; totalPedidos cuenta todos los estados del rango
 * — por eso pueden no coincidir, ver comentario en el controlador).
 * El filtro de fechas (filtrar) no usa Inertia sino una navegacion de
 * navegador normal (window.location.href) porque solo cambia el query
 * string de la misma ruta GET. Los botones de descarga NO piden datos
 * nuevos al servidor: generan el PDF/Excel en el navegador a partir de
 * los props que ya se cargaron con la pagina.
 */
import { router } from '@inertiajs/vue3'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'
import * as XLSX from 'xlsx'

const props = defineProps({
    totalVentas: Number,
    totalPedidos: Number,
    productosMasVendidos: Array,
    desde: String,
    hasta: String,
})

const formatPrice = (v) => '$' + Number(v).toLocaleString('es-CO')

const filtrar = () => {
    const desde = document.getElementById('desde').value
    const hasta = document.getElementById('hasta').value
    window.location.href = '/reports/ventas?desde=' + desde + '&hasta=' + hasta
}

const descargarPDF = () => {
    const doc = new jsPDF()
    doc.setFontSize(18)
    doc.text('Reporte de Ventas - Mi Chuzito', 14, 20)
    doc.setFontSize(11)
    doc.text('Periodo: ' + props.desde + ' al ' + props.hasta, 14, 30)
    doc.text('Total Vendido: ' + formatPrice(props.totalVentas), 14, 38)
    doc.text('Total Pedidos: ' + props.totalPedidos, 14, 46)

    autoTable(doc, {
        startY: 55,
        head: [['Producto', 'Cantidad Vendida', 'Total Ingresos']],
        body: props.productosMasVendidos.map(p => [p.name, p.total_vendido, formatPrice(p.total_ingresos)]),
    })

    doc.save('reporte-ventas-' + props.desde + '-' + props.hasta + '.pdf')
}

const descargarExcel = () => {
    const data = props.productosMasVendidos.map(p => ({
        Producto: p.name,
        'Cantidad Vendida': p.total_vendido,
        'Total Ingresos': p.total_ingresos,
    }))
    const resumen = [
        { Concepto: 'Total Vendido', Valor: props.totalVentas },
        { Concepto: 'Total Pedidos', Valor: props.totalPedidos },
        { Concepto: 'Periodo', Valor: props.desde + ' al ' + props.hasta },
    ]
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(resumen), 'Resumen')
    XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(data), 'Productos')
    XLSX.writeFile(wb, 'reporte-ventas-' + props.desde + '-' + props.hasta + '.xlsx')
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
        <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0 0 1.5rem">Reporte de Ventas</h1>

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

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem">
                <div style="font-size:12px;color:#64748b">Total Vendido</div>
                <div style="font-size:26px;font-weight:700;color:#16a34a;margin-top:4px">{{ formatPrice(totalVentas) }}</div>
            </div>
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem">
                <div style="font-size:12px;color:#64748b">Total Pedidos</div>
                <div style="font-size:26px;font-weight:700;color:#2563eb;margin-top:4px">{{ totalPedidos }}</div>
            </div>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;font-weight:700;color:#1e293b;font-size:15px">Productos mas vendidos</div>
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Producto</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Cantidad Vendida</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Total Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="producto in productosMasVendidos" :key="producto.name" style="border-bottom:1px solid #e2e8f0">
                        <td style="padding:10px 16px;font-weight:600;color:#1e293b">{{ producto.name }}</td>
                        <td style="padding:10px 16px;color:#64748b">{{ producto.total_vendido }}</td>
                        <td style="padding:10px 16px;color:#64748b">{{ formatPrice(producto.total_ingresos) }}</td>
                    </tr>
                    <tr v-if="productosMasVendidos.length === 0">
                        <td colspan="3" style="padding:2rem;text-align:center;color:#94a3b8">No hay datos en este periodo</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>
