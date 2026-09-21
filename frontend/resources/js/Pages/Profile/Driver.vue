<script setup>
import { useForm, router } from '@inertiajs/vue3'

const { user } = defineProps(['user'])
const logout = () => router.post('/logout')

const toggleForm = useForm({})
const toggleAvailability = () => toggleForm.patch('/perfil/disponibilidad')
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <!-- Navbar exclusivo repartidor, sin menu/carrito/pagos -->
    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/mis-entregas" style="font-size:13px;color:#64748b;text-decoration:none">Mis entregas</a>
            <span style="font-size:13px;padding:3px 10px;border-radius:20px;background:#f0fdf4;color:#059669;font-weight:500">Repartidor</span>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:700px;margin:2rem auto;padding:0 1rem">

        <!-- Encabezado perfil -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:2rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.5rem">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;font-size:28px;color:white;font-weight:700;flex-shrink:0">
                {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#1e293b">{{ user.name }}</div>
                <div style="font-size:14px;color:#64748b;margin-top:2px">{{ user.email ?? user.phone }}</div>
                <span style="display:inline-block;margin-top:6px;font-size:12px;padding:2px 10px;border-radius:20px;background:#f0fdf4;color:#059669;font-weight:500">Repartidor</span>
            </div>
        </div>

        <!-- RF-038: Disponibilidad -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between">
            <div style="display:flex;align-items:center;gap:12px">
                <div :style="`width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:${user.disponible ? '#f0fdf4' : '#fef2f2'}`">
                    <svg width="24" height="24" fill="none" :stroke="user.disponible ? '#15803d' : '#dc2626'" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <div style="font-size:15px;font-weight:600;color:#1e293b">
                        {{ user.disponible ? 'Disponible para entregas' : 'No disponible' }}
                    </div>
                    <div style="font-size:12px;color:#94a3b8">
                        {{ user.disponible ? 'Puedes recibir nuevas asignaciones' : 'No recibiras nuevas asignaciones' }}
                    </div>
                </div>
            </div>
            <button @click="toggleAvailability" :disabled="toggleForm.processing"
                :style="`padding:10px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;${user.disponible ? 'background:#fef2f2;color:#dc2626' : 'background:#f0fdf4;color:#15803d'}`">
                {{ user.disponible ? 'Marcar no disponible' : 'Marcar disponible' }}
            </button>
        </div>

        <!-- Acceso rapido a entregas -->
        <div @click="router.visit('/mis-entregas')"
            style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:2rem;cursor:pointer;display:flex;align-items:center;justify-content:space-between"
            @mouseover="$event.currentTarget.style.borderColor='#f97316'"
            @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
            <div style="display:flex;align-items:center;gap:14px">
                <svg width="32" height="32" fill="none" stroke="#f97316" stroke-width="1.5" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <div>
                    <div style="font-size:15px;font-weight:600;color:#1e293b">Ver mis entregas</div>
                    <div style="font-size:13px;color:#64748b">Pedidos asignados y su estado</div>
                </div>
            </div>
            <svg width="18" height="18" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </div>

    </div>
</div>
</template>