<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { auth, RecaptchaVerifier, signInWithPhoneNumber } from '@/firebase'

const step = ref('phone')
const phoneNumber = ref('')
const otpCode = ref('')
const loading = ref(false)
const error = ref('')
const success = ref('')
const countdown = ref(0)
const confirmationResult = ref(null)
let timer = null
let recaptchaVerifier = null

onMounted(() => {
    setupRecaptcha()
})

onUnmounted(() => {
    if (timer) clearInterval(timer)
    if (recaptchaVerifier) {
        recaptchaVerifier.clear()
        recaptchaVerifier = null
    }
})

const setupRecaptcha = () => {
    try {
        recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
            size: 'invisible',
            callback: () => {}
        })
    } catch (e) {
        console.log('reCAPTCHA setup error:', e)
    }
}

const sendOtp = async () => {
    if (!phoneNumber.value || phoneNumber.value.length < 10) {
        error.value = 'Ingresa un numero de telefono valido de 10 digitos.'
        return
    }

    let phone = phoneNumber.value.replace(/\s+/g, '').replace(/[^0-9+]/g, '')
    if (!phone.startsWith('+')) {
        phone = '+57' + phone
    }

    loading.value = true
    error.value = ''

    try {
        confirmationResult.value = await signInWithPhoneNumber(auth, phone, recaptchaVerifier)
        step.value = 'otp'
        success.value = `Codigo enviado a ${phone}`
        startCountdown()
    } catch (err) {
        console.error('SMS error:', err)
        if (err.code === 'auth/invalid-phone-number') {
            error.value = 'Numero de telefono invalido. Verifica el formato.'
        } else if (err.code === 'auth/too-many-requests') {
            error.value = 'Demasiados intentos. Espera unos minutos.'
        } else {
            error.value = 'Error al enviar el codigo: ' + err.message
        }
        // Reiniciar reCAPTCHA
        if (recaptchaVerifier) {
            recaptchaVerifier.clear()
            recaptchaVerifier = null
        }
        setupRecaptcha()
    } finally {
        loading.value = false
    }
}

const verifyOtp = async () => {
    if (!otpCode.value || otpCode.value.length < 6) {
        error.value = 'Ingresa el codigo de 6 digitos.'
        return
    }

    loading.value = true
    error.value = ''

    try {
        const result = await confirmationResult.value.confirm(otpCode.value)
        const token  = await result.user.getIdToken()

        // Enviar a Laravel
        const response = await fetch('/auth/sms/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({
                phone:          phoneNumber.value,
                firebase_token: token,
            })
        })

        if (response.redirected) {
            window.location.href = response.url
        } else {
            router.visit('/')
        }

    } catch (err) {
        console.error('OTP error:', err)
        if (err.code === 'auth/invalid-verification-code') {
            error.value = 'Codigo incorrecto. Verifica e intenta de nuevo.'
        } else if (err.code === 'auth/code-expired') {
            error.value = 'El codigo expiro. Solicita uno nuevo.'
        } else {
            error.value = 'Error al verificar: ' + err.message
        }
    } finally {
        loading.value = false
    }
}

const startCountdown = () => {
    countdown.value = 60
    timer = setInterval(() => {
        countdown.value--
        if (countdown.value <= 0) clearInterval(timer)
    }, 1000)
}

const resendOtp = () => {
    step.value = 'phone'
    otpCode.value = ''
    error.value = ''
    success.value = ''
    if (recaptchaVerifier) {
        recaptchaVerifier.clear()
        recaptchaVerifier = null
    }
    setupRecaptcha()
}
</script>

<template>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-family:'Segoe UI',sans-serif">
    <div id="recaptcha-container"></div>

    <div style="width:100%;max-width:440px;background:white;border-radius:20px;border:1px solid #e2e8f0;padding:2.5rem;box-shadow:0 8px 40px rgba(0,0,0,0.08)">

        <div style="text-align:center;margin-bottom:2rem">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                <svg width="30" height="30" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.95 12a19.79 19.79 0 01-3.07-8.67A2 2 0 012.86 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.09 8.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:6px">
                {{ step === 'phone' ? 'Verificacion por SMS' : 'Ingresa el codigo' }}
            </h1>
            <p style="font-size:13px;color:#64748b;line-height:1.6">
                {{ step === 'phone' ? 'Te enviaremos un codigo de verificacion a tu celular.' : 'Ingresa el codigo de 6 digitos que enviamos a tu celular.' }}
            </p>
        </div>

        <div v-if="error" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ error }}
        </div>

        <div v-if="success" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ success }}
        </div>

        <!-- Paso 1: Telefono -->
        <div v-if="step === 'phone'">
            <div style="margin-bottom:1.5rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Numero de telefono (10 digitos)</label>
                <div style="display:flex;align-items:center;border:1.5px solid #e2e8f0;border-radius:8px;margin-top:6px;overflow:hidden"
                    @focusin="$event.currentTarget.style.borderColor='#f97316'"
                    @focusout="$event.currentTarget.style.borderColor='#e2e8f0'">
                    <div style="padding:12px;background:#f8fafc;border-right:1px solid #e2e8f0;font-size:14px;font-weight:500;color:#64748b;white-space:nowrap">+57</div>
                    <input v-model="phoneNumber" type="tel" placeholder="3001234567"
                        style="flex:1;padding:12px;border:none;font-size:15px;outline:none;background:white"
                        maxlength="10"
                        @keyup.enter="sendOtp"/>
                </div>
                <span style="font-size:11px;color:#94a3b8;margin-top:4px;display:block">Ejemplo: 3001234567</span>
            </div>

            <button @click="sendOtp" :disabled="loading"
                :style="`width:100%;padding:13px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;${loading ? 'background:#e2e8f0;color:#94a3b8;cursor:not-allowed' : 'background:linear-gradient(135deg,#f97316,#ea580c);color:white'}`">
                {{ loading ? 'Enviando...' : 'Enviar codigo SMS' }}
            </button>
        </div>

        <!-- Paso 2: OTP -->
        <div v-if="step === 'otp'">
            <div style="margin-bottom:1.5rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Codigo de verificacion</label>
                <input v-model="otpCode" type="text" maxlength="6" placeholder="000000"
                    style="width:100%;padding:16px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:28px;outline:none;box-sizing:border-box;margin-top:8px;text-align:center;letter-spacing:12px;font-weight:700;color:#1e293b"
                    @focus="$event.target.style.borderColor='#f97316'"
                    @blur="$event.target.style.borderColor='#e2e8f0'"
                    @keyup.enter="verifyOtp"/>
            </div>

            <button @click="verifyOtp" :disabled="loading || otpCode.length < 6"
                :style="`width:100%;padding:13px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:1rem;${loading || otpCode.length < 6 ? 'background:#e2e8f0;color:#94a3b8;cursor:not-allowed' : 'background:linear-gradient(135deg,#f97316,#ea580c);color:white'}`">
                {{ loading ? 'Verificando...' : 'Verificar codigo' }}
            </button>

            <div style="text-align:center">
                <span style="font-size:13px;color:#64748b">No recibiste el codigo? </span>
                <button v-if="countdown <= 0" @click="resendOtp"
                    style="background:none;border:none;color:#f97316;font-size:13px;font-weight:600;cursor:pointer;padding:0">
                    Reenviar
                </button>
                <span v-else style="font-size:13px;color:#94a3b8">Reenviar en {{ countdown }}s</span>
            </div>
        </div>

        <div style="text-align:center;margin-top:1.5rem">
            <a href="/login" style="font-size:13px;color:#64748b;text-decoration:none">Volver al inicio de sesion</a>
        </div>

    </div>
</div>
</template>