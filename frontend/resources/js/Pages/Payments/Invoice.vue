<script setup>
import { router } from '@inertiajs/vue3'

const { invoice } = defineProps(['invoice'])
const logout = () => router.post('/logout')
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <!-- Navbar -->
    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/dashboard" style="font-size:13px;color:#64748b;text-decoration:none">Dashboard</a>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:700px;margin:2rem auto;padding:0 1rem">

        <!-- Factura -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden" id="invoice-content">

            <!-- Encabezado factura -->
            <div style="background:linear-gradient(135deg,#f97316,#ea580c);padding:2rem;display:flex;justify-content:space-between;align-items:center">
                <div>
                    <div style="font-size:22px;font-weight:700;color:white">Mi Chuzito</div>
                    <div style="font-size:13px;color:rgba(255,255,255,0.85)">Factura Electronica</div>
                </div>
                <div style="text-align:right">
                    <div style="font-size:20px;font-weight:700;color:white">{{ invoice.invoice_number }}</div>
                    <div style="font-size:12px;color:rgba(255,255,255,0.85)">
                        {{ new Date(invoice.issued_at).toLocaleDateString('es-CO') }}
                    </div>
                    <span :style="`font-size:11px;padding:2px 10px;border-radius:20px;font-weight:500;${invoice.status === 'activa' ? 'background:rgba(255,255,255,0.3);color:white' : 'background:#fef2f2;color:#dc2626'}`">
                        {{ invoice.status.toUpperCase() }}
                    </span>
                </div>
            </div>

            <div style="padding:2rem">

                <!-- Datos cliente -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem">
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">Datos del cliente</div>
                        <div style="font-size:14px;font-weight:600;color:#1e293b">{{ invoice.client_name }}</div>
                        <div style="font-size:13px;color:#64748b">{{ invoice.client_email }}</div>
                        <div style="font-size:13px;color:#64748b">{{ invoice.client_phone }}</div>
                        <div v-if="invoice.client_document" style="font-size:13px;color:#64748b">Doc: {{ invoice.client_document }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px">Informacion de pago</div>
                        <div style="font-size:14px;font-weight:600;color:#1e293b">Metodo: {{ invoice.payment?.method }}</div>
                        <div style="font-size:13px;color:#64748b">Estado: {{ invoice.payment?.status }}</div>
                        <div v-if="invoice.payment?.reference" style="font-size:13px;color:#64748b">Ref: {{ invoice.payment.reference }}</div>
                    </div>
                </div>

                <!-- Detalle productos -->
                <table style="width:100%;border-collapse:collapse;margin-bottom:1.5rem">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th style="padding:10px 12px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Descripcion</th>
                            <th style="padding:10px 12px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Cant.</th>
                            <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Valor unit.</th>
                            <th style="padding:10px 12px;text-align:right;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in invoice.items" :key="item.descripcion" style="border-top:1px solid #f1f5f9">
                            <td style="padding:12px;font-size:14px;color:#1e293b">{{ item.descripcion }}</td>
                            <td style="padding:12px;font-size:14px;color:#1e293b;text-align:center">{{ item.cantidad }}</td>
                            <td style="padding:12px;font-size:14px;color:#1e293b;text-align:right">${{ Number(item.valor_unitario).toLocaleString('es-CO') }}</td>
                            <td style="padding:12px;font-size:14px;color:#1e293b;text-align:right">${{ Number(item.subtotal).toLocaleString('es-CO') }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totales -->
                <div style="border-top:2px solid #e2e8f0;padding-top:1rem">
                    <div style="display:flex;justify-content:flex-end">
                        <div style="width:250px">
                            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:#64748b">
                                <span>Subtotal</span>
                                <span>${{ Number(invoice.subtotal).toLocaleString('es-CO') }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:#64748b">
                                <span>IVA (19%)</span>
                                <span>${{ Number(invoice.tax).toLocaleString('es-CO') }}</span>
                            </div>
                            <div style="display:flex;justify-content:space-between;padding:10px 0;font-size:16px;font-weight:700;color:#1e293b;border-top:1px solid #e2e8f0;margin-top:4px">
                                <span>Total</span>
                                <span style="color:#f97316">${{ Number(invoice.total).toLocaleString('es-CO') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nota legal -->
                <div style="margin-top:2rem;padding:12px;background:#f8fafc;border-radius:8px;font-size:12px;color:#94a3b8;text-align:center">
                    Esta factura es un documento electronico valido. No requiere firma fisica. Numero consecutivo unico garantizado por el sistema.
                </div>

            </div>
        </div>

        <!-- Botones -->
        <div style="display:flex;gap:10px;margin-top:1rem">
            <button @click="router.visit('/dashboard')"
                style="flex:1;padding:12px;border:1.5px solid #e2e8f0;border-radius:8px;background:white;font-size:14px;font-weight:500;cursor:pointer;color:#64748b">
                Volver al inicio
            </button>
            <button onclick="window.print()"
                style="flex:1;padding:12px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
                Imprimir factura
            </button>
        </div>

    </div>
</div>
</template>