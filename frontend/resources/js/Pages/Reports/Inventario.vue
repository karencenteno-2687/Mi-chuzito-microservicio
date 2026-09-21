<script setup>
/**
 * Reports/Inventario.vue — Reporte de Inventario.
 *
 * A diferencia de Ventas/Pedidos/Tiempos, no tiene filtro de fechas:
 * ReportController::inventario() siempre devuelve el estado actual
 * (una "foto" del momento), así que aquí no hay input de rango ni
 * botón "Filtrar", solo descarga de PDF/Excel con lo ya cargado.
 */
import { router } from '@inertiajs/vue3'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'
import * as XLSX from 'xlsx'

const props = defineProps({
    productos: Array,
    disponibles: Number,
    agotados: Number,
})

const descargarPDF = () => {
    const doc = new jsPDF()
    doc.setFontSize(18)
    doc.text('Reporte de Inventario - Mi Chuzito', 14, 20)
    doc.setFontSize(11)
    doc.text('Fecha: ' + new Date().toLocaleDateString('es-CO'), 14, 30)
    doc.text('Disponibles: ' + props.disponibles, 14, 38)
    doc.text('Agotados: ' + props.agotados, 14, 46)

    autoTable(doc, {
        startY: 55,
        head: [['Producto', 'Categoria', 'Precio', 'Stock', 'Estado']],
        body: props.productos.map(p => [p.name, p.category, '$' + p.price, p.stock, p.is_available ? 'Disponible' : 'Agotado']),
    })

    doc.save('reporte-inventario.pdf')
}

const descargarExcel = () => {
    const data = props.productos.map(p => ({
        Producto: p.name,
        Categoria: p.category,
        Precio: p.price,
        Stock: p.stock,
        Estado: p.is_available ? 'Disponible' : 'Agotado',
    }))
    const wb = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(data), 'Inventario')
    XLSX.writeFile(wb, 'reporte-inventario.xlsx')
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
        <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0 0 1.5rem">Reporte de Inventario</h1>

        <div style="margin-bottom:1.5rem;display:flex;gap:12px">
            <button @click="descargarPDF" style="padding:9px 18px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Descargar PDF</button>
            <button @click="descargarExcel" style="padding:9px 18px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Descargar Excel</button>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem">
                <div style="font-size:12px;color:#64748b">Productos Disponibles</div>
                <div style="font-size:26px;font-weight:700;color:#16a34a;margin-top:4px">{{ disponibles }}</div>
            </div>
            <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem">
                <div style="font-size:12px;color:#64748b">Productos Agotados</div>
                <div style="font-size:26px;font-weight:700;color:#dc2626;margin-top:4px">{{ agotados }}</div>
            </div>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;font-weight:700;color:#1e293b;font-size:15px">Estado del inventario</div>
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Producto</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Categoria</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Precio</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Stock</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="producto in productos" :key="producto.name" style="border-bottom:1px solid #e2e8f0">
                        <td style="padding:10px 16px;font-weight:600;color:#1e293b">{{ producto.name }}</td>
                        <td style="padding:10px 16px;color:#64748b">{{ producto.category }}</td>
                        <td style="padding:10px 16px;color:#64748b">${{ producto.price }}</td>
                        <td style="padding:10px 16px;color:#64748b">{{ producto.stock }}</td>
                        <td style="padding:10px 16px">
                            <span v-if="producto.is_available" style="background:#f0fdf4;color:#15803d;padding:2px 10px;border-radius:20px;font-size:11px">Disponible</span>
                            <span v-else style="background:#fef2f2;color:#dc2626;padding:2px 10px;border-radius:20px;font-size:11px">Agotado</span>
                        </td>
                    </tr>
                    <tr v-if="productos.length === 0">
                        <td colspan="5" style="padding:2rem;text-align:center;color:#94a3b8">No hay productos registrados</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>
