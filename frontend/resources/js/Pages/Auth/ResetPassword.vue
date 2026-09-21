<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    code:                  '',
    password:              '',
    password_confirmation: '',
})

const submit = () => form.post('/reset-password')
</script>

<template>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f8fafc;font-family:'Segoe UI',sans-serif">
    <div style="width:100%;max-width:420px;background:white;border-radius:16px;border:1px solid #e2e8f0;padding:2.5rem">

        <div style="text-align:center;margin-bottom:2rem">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                <svg width="26" height="26" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin-bottom:6px">Nueva contrasena</h1>
            <p style="font-size:13px;color:#64748b;line-height:1.5">Ingresa el codigo que enviamos a tu correo y tu nueva contrasena.</p>
        </div>

        <div v-if="form.errors.code" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
            {{ form.errors.code }}
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Codigo de verificacion</label>
            <input v-model="form.code" type="text" maxlength="6" placeholder="000000"
                style="width:100%;padding:14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:24px;outline:none;box-sizing:border-box;margin-top:6px;text-align:center;letter-spacing:10px;font-weight:600"
                @focus="$event.target.style.borderColor='#f97316'"
                @blur="$event.target.style.borderColor='#e2e8f0'"/>
        </div>

        <div style="margin-bottom:1rem">
            <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Nueva contrasena</label>
            <input v-model="form.password" type="password" placeholder="Min. 8 caracteres"
                style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                @focus="$event.target.style.borderColor='#f97316'"
                @blur="$event.target.style.borderColor='#e2e8f0'"/>
            <span v-if="form.errors.password" style="color:#dc2626;font-size:11px">{{ form.errors.password }}</span>
        </div>

        <div style="margin-bottom:1.5rem">
            <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Confirmar contrasena</label>
            <input v-model="form.password_confirmation" type="password" placeholder="Repite la contrasena"
                style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                @focus="$event.target.style.borderColor='#f97316'"
                @blur="$event.target.style.borderColor='#e2e8f0'"/>
        </div>

        <button @click="submit" :disabled="form.processing"
            style="width:100%;padding:13px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer">
            {{ form.processing ? 'Restableciendo...' : 'Restablecer contrasena' }}
        </button>

    </div>
</div>
</template>