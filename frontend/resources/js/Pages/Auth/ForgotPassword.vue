<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({ email: '' })
const submit = () => form.post('/forgot-password')
</script>

<template>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-family:'Segoe UI',sans-serif">
    <div style="width:100%;max-width:420px;background:white;border-radius:16px;border:1px solid #e2e8f0;padding:2.5rem">

        <div style="text-align:center;margin-bottom:2rem">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                <svg width="26" height="26" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:6px">Recuperar contrasena</h1>
            <p style="font-size:13px;color:#64748b;line-height:1.5">Ingresa tu correo y te enviaremos un codigo para restablecer tu contrasena.</p>
        </div>

        <div v-if="form.errors.email" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ form.errors.email }}
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Correo electronico</label>
            <input v-model="form.email" type="email" placeholder="correo@ejemplo.com"
                style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                @focus="$event.target.style.borderColor='#f97316'"
                @blur="$event.target.style.borderColor='#e2e8f0'"/>
        </div>

        <button @click="submit" :disabled="form.processing"
            style="width:100%;padding:13px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:1rem">
            {{ form.processing ? 'Enviando...' : 'Enviar codigo' }}
        </button>

        <div style="text-align:center">
            <a href="/login" style="font-size:13px;color:#f97316;text-decoration:none;font-weight:500">Volver al inicio de sesion</a>
        </div>

    </div>
</div>
</template>