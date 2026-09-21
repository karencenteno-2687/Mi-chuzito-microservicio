<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3'

const { user, order } = defineProps(['user', 'order'])

const logout = () => router.post('/logout')
const role = usePage().props.auth.user.roles?.[0] ?? 'sin rol'

const form = useForm({
    method: 'efectivo',
    notes:  '',
})

const submitEfectivo = () => form.post(`/pagos/${order.id}`)

const formatCOP = (value) => Number(value).toLocaleString('es-CO')
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
            <a href="/perfil" style="font-size:13px;color:#64748b;text-decoration:none">Mi perfil</a>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:600px;margin:2rem auto;padding:0 1rem">

        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Realizar pago</h1>
            <p style="font-size:13px;color:#64748b;margin:4px 0 0">Pedido #{{ order.id }} — elige como pagar</p>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem">
            <h2 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:1rem">Resumen del pedido</h2>

            <div v-for="item in order.items" :key="item.id" style="display:flex;justify-content:space-between;font-size:13px;color:#334155;margin-bottom:8px">
                <span>{{ item.qty }}x {{ item.name }}</span>
                <span>${{ formatCOP(item.subtotal) }}</span>
            </div>

            <div style="border-top:1px solid #e2e8f0;margin-top:10px;padding-top:10px">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:4px">
                    <span>Subtotal</span><span>${{ formatCOP(order.subtotal) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:8px">
                    <span>IVA (19%)</span><span>${{ formatCOP(order.tax) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:#1e293b">
                    <span>Total a pagar</span><span>${{ formatCOP(order.total) }}</span>
                </div>
            </div>
        </div>

        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:2rem;margin-bottom:1.5rem">
            <h2 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:1rem">Metodo de pago</h2>

            <div style="margin-bottom:1.5rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Monto a pagar</label>
                <div style="margin-top:6px;padding:11px 12px;background:#f1f5f9;border:1.5px solid #e2e8f0;border-radius:8px;font-size:16px;font-weight:700;color:#1e293b">
                    ${{ formatCOP(order.total) }}
                </div>
                <span style="font-size:11px;color:#94a3b8;display:block;margin-top:4px">Este monto corresponde al total real de tu pedido y no puede modificarse.</span>
            </div>

            <div style="margin-bottom:1.5rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Notas adicionales (opcional)</label>
                <textarea v-model="form.notes" placeholder="Informacion adicional..."
                    style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px;resize:vertical;min-height:80px"
                    @focus="$event.target.style.borderColor='#f97316'"
                    @blur="$event.target.style.borderColor='#e2e8f0'"></textarea>
            </div>

            <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px">
                <svg width="20" height="20" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <div>
                    <div style="font-size:12px;font-weight:600;color:#1e293b">Pago seguro</div>
                    <div style="font-size:11px;color:#64748b">Tus datos estan protegidos con encriptacion</div>
                </div>
            </div>

            <button @click="submitEfectivo" :disabled="form.processing"
                style="width:100%;padding:13px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer">
                {{ form.processing ? 'Procesando...' : 'Pagar en efectivo' }}
            </button>

            <div style="display:flex;align-items:center;gap:10px;margin:1rem 0">
                <div style="flex:1;height:1px;background:#e2e8f0"></div>
                <span style="font-size:12px;color:#94a3b8">o paga con</span>
                <div style="flex:1;height:1px;background:#e2e8f0"></div>
            </div>

            <a :href="`/pagos/${order.id}/paypal`"
                style="display:block;width:100%;padding:13px;background:#ffc439;color:#003087;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;text-align:center;text-decoration:none;box-sizing:border-box">
                Pagar con PayPal
            </a>
        </div>

    </div>
</div>
</template>
