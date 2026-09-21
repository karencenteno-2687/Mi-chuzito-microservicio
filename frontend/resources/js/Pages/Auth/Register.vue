<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
})

const submit = () => form.post('/register')
</script>

<template>
    <div style="max-width:400px;margin:80px auto;padding:2rem;border:1px solid #ddd;border-radius:8px">
        <h1 style="margin-bottom:1.5rem">Registro</h1>

        <div style="margin-bottom:1rem">
            <label>Nombre completo</label><br>
            <input v-model="form.name" type="text" required
                style="width:100%;padding:8px;margin-top:4px;border:1px solid #ccc;border-radius:4px"/>
            <span v-if="form.errors.name" style="color:red;font-size:12px">{{ form.errors.name }}</span>
        </div>

        <div style="margin-bottom:1rem">
            <label>Correo electrónico</label><br>
            <input v-model="form.email" type="email"
                style="width:100%;padding:8px;margin-top:4px;border:1px solid #ccc;border-radius:4px"/>
            <span v-if="form.errors.email" style="color:red;font-size:12px">{{ form.errors.email }}</span>
        </div>

        <div style="margin-bottom:1rem">
            <label>Teléfono</label><br>
            <input v-model="form.phone" type="text"
                style="width:100%;padding:8px;margin-top:4px;border:1px solid #ccc;border-radius:4px"/>
            <span v-if="form.errors.phone" style="color:red;font-size:12px">{{ form.errors.phone }}</span>
        </div>

        <div style="margin-bottom:1rem">
            <label>Contraseña</label><br>
            <input v-model="form.password" type="password" required
                style="width:100%;padding:8px;margin-top:4px;border:1px solid #ccc;border-radius:4px"/>
            <span v-if="form.errors.password" style="color:red;font-size:12px">{{ form.errors.password }}</span>
        </div>

        <div style="margin-bottom:1rem">
            <label>Confirmar contraseña</label><br>
            <input v-model="form.password_confirmation" type="password" required
                style="width:100%;padding:8px;margin-top:4px;border:1px solid #ccc;border-radius:4px"/>
        </div>

        <button @click="submit" :disabled="form.processing"
            style="width:100%;padding:10px;background:#4f46e5;color:white;border:none;border-radius:4px;cursor:pointer">
            {{ form.processing ? 'Registrando...' : 'Registrarse' }}
        </button>

        <p style="margin-top:1rem;text-align:center">
            <a href="/login">¿Ya tienes cuenta? Inicia sesión</a>
        </p>
    </div>
</template>