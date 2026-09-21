<script setup>
import { router } from '@inertiajs/vue3'

const { payment } = defineProps(['payment'])
const logout = () => router.post('/logout')
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif;display:flex;align-items:center;justify-content:center">
    <div style="max-width:480px;width:100%;background:white;border-radius:16px;border:1px solid #e2e8f0;padding:2.5rem;text-align:center">

        <div style="width:64px;height:64px;background:#fff7ed;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem">
            <svg width="32" height="32" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>

        <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:8px">Pago pendiente</h1>
        <p style="font-size:14px;color:#64748b;line-height:1.6;margin-bottom:2rem">
            Tu pago en efectivo ha sido registrado. El administrador confirmara la recepcion del dinero y tu factura se generara automaticamente.
        </p>

        <div style="background:#f8fafc;border-radius:10px;padding:1.5rem;margin-bottom:2rem;text-align:left">
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;border-bottom:1px solid #e2e8f0">
                <span style="color:#64748b">Metodo</span>
                <span style="font-weight:500;color:#1e293b;text-transform:capitalize">{{ payment.method }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;border-bottom:1px solid #e2e8f0">
                <span style="color:#64748b">Monto</span>
                <span style="font-weight:500;color:#1e293b">${{ Number(payment.amount).toLocaleString('es-CO') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px">
                <span style="color:#64748b">Estado</span>
                <span style="font-weight:500;color:#f97316;text-transform:capitalize">{{ payment.status }}</span>
            </div>
        </div>

        <p style="font-size:13px;color:#94a3b8;margin-bottom:1.5rem">Recibiras un correo cuando tu pago sea confirmado.</p>

        <button @click="router.visit('/dashboard')"
            style="width:100%;padding:12px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
            Volver al inicio
        </button>

    </div>
</div>
</template>
