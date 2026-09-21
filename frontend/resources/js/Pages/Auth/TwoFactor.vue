<script setup>
import { useForm, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps(['seconds_until_resend', 'attempts_left'])
const form = useForm({ code: '' })
const resendForm = useForm({})
const countdown = ref(props.seconds_until_resend || 60)
const canResend = ref(countdown.value <= 0)
let timer = null

onMounted(() => {
    if (countdown.value > 0) startCountdown()
    else canResend.value = true
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
})

const startCountdown = () => {
    canResend.value = false
    timer = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) {
            canResend.value = true
            clearInterval(timer)
        }
    }, 1000)
}

const submit = () => form.post('/2fa/verify')

const resend = () => {
    resendForm.post('/2fa/resend', {
        onSuccess: () => {
            countdown.value = 60
            startCountdown()
        }
    })
}
</script>

<template>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-family:'Segoe UI',sans-serif">
    <div style="width:100%;max-width:440px;background:white;border-radius:20px;border:1px solid #e2e8f0;padding:2.5rem;box-shadow:0 8px 40px rgba(0,0,0,0.08)">

        <div style="text-align:center;margin-bottom:2rem">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                <svg width="30" height="30" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:6px">Verificacion en dos pasos</h1>
            <p style="font-size:13px;color:#64748b;line-height:1.6">Ingresa el codigo de 6 digitos que enviamos a tu correo electronico.</p>
        </div>

        <!-- Intentos restantes -->
        <div v-if="attempts_left <= 3" style="background:#fff7ed;border:1px solid #fed7aa;color:#c2410c;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem;display:flex;align-items:center;gap:8px">
            <svg width="16" height="16" fill="none" stroke="#c2410c" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Te quedan {{ attempts_left }} intentos antes de bloquear el acceso.
        </div>

        <!-- Error -->
        <div v-if="form.errors.code" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ form.errors.code }}
        </div>

        <!-- Exito reenvio -->
        <div v-if="$page.props.success" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ $page.props.success }}
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Codigo de verificacion</label>
            <input v-model="form.code" type="text" maxlength="6" placeholder="000000"
                style="width:100%;padding:16px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:28px;outline:none;box-sizing:border-box;margin-top:8px;text-align:center;letter-spacing:12px;font-weight:700;color:#1e293b"
                @focus="$event.target.style.borderColor='#f97316'"
                @blur="$event.target.style.borderColor='#e2e8f0'"/>
        </div>

        <button @click="submit" :disabled="form.processing || form.code.length < 6"
            :style="`width:100%;padding:13px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:1rem;transition:opacity 0.2s;${form.processing || form.code.length < 6 ? 'background:#e2e8f0;color:#94a3b8;cursor:not-allowed' : 'background:linear-gradient(135deg,#f97316,#ea580c);color:white'}`">
            {{ form.processing ? 'Verificando...' : 'Verificar codigo' }}
        </button>

        <div style="text-align:center">
            <span style="font-size:13px;color:#64748b">No recibiste el codigo? </span>
            <button v-if="canResend" @click="resend" :disabled="resendForm.processing"
                style="background:none;border:none;color:#f97316;font-size:13px;font-weight:600;cursor:pointer;padding:0">
                {{ resendForm.processing ? 'Enviando...' : 'Reenviar codigo' }}
            </button>
            <span v-else style="font-size:13px;color:#94a3b8">Reenviar en {{ countdown }}s</span>
        </div>

        <div style="text-align:center;margin-top:1rem">
            <a href="/login" style="font-size:13px;color:#64748b;text-decoration:none">Volver al inicio de sesion</a>
        </div>

    </div>
</div>
</template>