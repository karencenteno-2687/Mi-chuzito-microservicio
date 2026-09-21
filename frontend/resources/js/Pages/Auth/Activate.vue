<script setup>
import { useForm, router, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const form = useForm({ code: '' })
const resendForm = useForm({})
const countdown = ref(60)
const canResend = ref(false)
let timer = null

const { props } = usePage()

onMounted(() => {
    startCountdown()
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})

const startCountdown = () => {
    countdown.value = 60
    canResend.value = false
    timer = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
            canResend.value = true
            clearInterval(timer)
        }
    }, 1000)
}

const submit = () => form.post('/activate')

const resend = () => {
    resendForm.post('/activate/resend', {
        onSuccess: () => startCountdown()
    })
}
</script>

<template>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-family:'Segoe UI',sans-serif">
    <div style="width:100%;max-width:440px;background:white;border-radius:20px;border:1px solid #e2e8f0;padding:2.5rem;box-shadow:0 8px 40px rgba(0,0,0,0.08)">

        <!-- Icono -->
        <div style="text-align:center;margin-bottom:2rem">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                <svg width="30" height="30" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:6px">Activa tu cuenta</h1>
            <p style="font-size:13px;color:#64748b;line-height:1.6">Te enviamos un codigo de 6 digitos a tu correo electronico. Ingresalo para activar tu cuenta.</p>
        </div>

        <!-- Mensaje exito -->
        <div v-if="$page.props.success" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ $page.props.success }}
        </div>

        <!-- Error -->
        <div v-if="form.errors.code" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ form.errors.code }}
        </div>

        <!-- Input codigo -->
        <div style="margin-bottom:1.5rem">
            <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Codigo de activacion</label>
            <input v-model="form.code" type="text" maxlength="6" placeholder="000000"
                style="width:100%;padding:16px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:28px;outline:none;box-sizing:border-box;margin-top:8px;text-align:center;letter-spacing:12px;font-weight:700;color:#1e293b"
                @focus="$event.target.style.borderColor='#f97316'"
                @blur="$event.target.style.borderColor='#e2e8f0'"/>
        </div>

        <!-- Boton verificar -->
        <button @click="submit" :disabled="form.processing || form.code.length < 6"
            :style="`width:100%;padding:13px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:1rem;transition:opacity 0.2s;${form.processing || form.code.length < 6 ? 'background:#e2e8f0;color:#94a3b8;cursor:not-allowed' : 'background:linear-gradient(135deg,#f97316,#ea580c);color:white'}`">
            {{ form.processing ? 'Verificando...' : 'Activar cuenta' }}
        </button>

        <!-- Reenviar -->
        <div style="text-align:center">
            <span style="font-size:13px;color:#64748b">No recibiste el codigo? </span>
            <button v-if="canResend" @click="resend" :disabled="resendForm.processing"
                style="background:none;border:none;color:#f97316;font-size:13px;font-weight:600;cursor:pointer;padding:0">
                {{ resendForm.processing ? 'Enviando...' : 'Reenviar codigo' }}
            </button>
            <span v-else style="font-size:13px;color:#94a3b8">Reenviar en {{ countdown }}s</span>
        </div>

        <!-- Volver -->
        <div style="text-align:center;margin-top:1rem">
            <a href="/login" style="font-size:13px;color:#64748b;text-decoration:none">Volver al inicio de sesion</a>
        </div>

    </div>
</div>
</template>