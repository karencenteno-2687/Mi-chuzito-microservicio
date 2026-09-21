<script setup>
import { usePage, router } from '@inertiajs/vue3'

const { auth } = usePage().props
const logout = () => router.post('/logout')
const role = auth.user.roles?.[0] ?? 'sin rol'

const roleColors = { administrador: '#7c3aed', cliente: '#0891b2', repartidor: '#059669', vendedor: '#f97316' }
const roleColor   = roleColors[role] ?? '#6b7280'
const roleBg      = { administrador: '#f3e8ff', cliente: '#e0f2fe', repartidor: '#f0fdf4', vendedor: '#fff7ed' }
const roleBgColor = roleBg[role] ?? '#f1f5f9'
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100">
        <div style="display:flex;align-items:center;gap:10px;cursor:pointer" @click="router.visit('/')">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <span style="font-size:13px;color:#64748b">{{ auth.user.email }}</span>
            <span :style="`font-size:12px;padding:3px 10px;border-radius:20px;font-weight:500;background:${roleBgColor};color:${roleColor}`">{{ role }}</span>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1100px;margin:0 auto;padding:2rem 1rem">

        <div style="background:linear-gradient(135deg,#f97316,#ea580c);border-radius:16px;padding:2rem;margin-bottom:2rem;display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-size:22px;font-weight:700;color:white;margin-bottom:4px">Bienvenido, {{ auth.user.name }}</div>
                <div style="font-size:14px;color:rgba(255,255,255,0.85)">Tienes acceso como <strong>{{ role }}</strong></div>
            </div>
            <div style="width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:26px;color:white;font-weight:700">
                {{ auth.user.name.charAt(0).toUpperCase() }}
            </div>
        </div>

        <!-- ADMINISTRADOR -->
        <div v-if="role === 'administrador'" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
            <div v-for="card in [
                { title: 'Usuarios', desc: 'Gestionar cuentas y roles', icon: 'users', color: '#7c3aed', bg: '#f3e8ff', route: '/admin/users' },
                { title: 'Pedidos', desc: 'Ver y asignar pedidos', icon: 'orders', color: '#f97316', bg: '#fff7ed', route: '/admin/orders' },
                { title: 'Inventario', desc: 'Productos y stock', icon: 'inventory', color: '#059669', bg: '#f0fdf4', route: '/admin/inventory' },
                { title: 'Pagos', desc: 'Aprobar y gestionar pagos', icon: 'payments', color: '#dc2626', bg: '#fef2f2', route: '/admin/payments' },
                { title: 'Reportes', desc: 'Ventas, inventario y entregas', icon: 'reports', color: '#0d9488', bg: '#f0fdfa', route: '/reports' },
                { title: 'Mi perfil', desc: 'Editar informacion personal', icon: 'profile', color: '#0891b2', bg: '#e0f2fe', route: '/perfil' },
            ]" :key="card.title"
            @click="router.visit(card.route)"
            style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer;transition:all 0.2s"
            @mouseover="$event.currentTarget.style.borderColor='#f97316';$event.currentTarget.style.transform='translateY(-2px)'"
            @mouseout="$event.currentTarget.style.borderColor='#e2e8f0';$event.currentTarget.style.transform='none'">
                <div :style="`width:44px;height:44px;background:${card.bg};border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px`">
                    <svg v-if="card.icon==='users'" width="22" height="22" fill="none" :stroke="card.color" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    <svg v-if="card.icon==='orders'" width="22" height="22" fill="none" :stroke="card.color" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <svg v-if="card.icon==='inventory'" width="22" height="22" fill="none" :stroke="card.color" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                    <svg v-if="card.icon==='payments'" width="22" height="22" fill="none" :stroke="card.color" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    <svg v-if="card.icon==='profile'" width="22" height="22" fill="none" :stroke="card.color" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <svg v-if="card.icon==='reports'" width="22" height="22" fill="none" :stroke="card.color" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">{{ card.title }}</div>
                <div style="font-size:13px;color:#64748b">{{ card.desc }}</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver â†’</div>
            </div>
        </div>

        <!-- CLIENTE -->
        <div v-if="role === 'cliente'" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
            <div @click="router.visit('/')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Hacer un pedido</div>
                <div style="font-size:13px;color:#64748b">Ver el menu y ordenar</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver menu â†’</div>
            </div>
            <div @click="router.visit('/orders')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#e0f2fe;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#0891b2" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Mis pedidos</div>
                <div style="font-size:13px;color:#64748b">Historial y estado</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver pedidos â†’</div>
            </div>
            <div @click="router.visit('/pagos')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#fef2f2;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Pagos</div>
                <div style="font-size:13px;color:#64748b">Realizar y ver pagos</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver pagos â†’</div>
            </div>
            <div @click="router.visit('/perfil')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Mi perfil</div>
                <div style="font-size:13px;color:#64748b">Editar informacion</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver perfil â†’</div>
            </div>
        </div>

        <!-- REPARTIDOR -->
        <div v-if="role === 'repartidor'" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
            <div @click="router.visit('/mis-entregas')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Mis entregas</div>
                <div style="font-size:13px;color:#64748b">Pedidos asignados a ti</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver entregas â†’</div>
            </div>
            <div @click="router.visit('/perfil')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Mi perfil</div>
                <div style="font-size:13px;color:#64748b">Editar informacion</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver perfil â†’</div>
            </div>
        </div>

        <!-- VENDEDOR -->
        <div v-if="role === 'vendedor'" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem">
            <div @click="router.visit('/mis-pedidos-vendedor')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Pedidos</div>
                <div style="font-size:13px;color:#64748b">Gestionar pedidos</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver pedidos â†’</div>
            </div>
            <div @click="router.visit('/perfil')" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;cursor:pointer" @mouseover="$event.currentTarget.style.borderColor='#f97316'" @mouseout="$event.currentTarget.style.borderColor='#e2e8f0'">
                <div style="width:44px;height:44px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px">
                    <svg width="22" height="22" fill="none" stroke="#059669" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:4px">Mi perfil</div>
                <div style="font-size:13px;color:#64748b">Editar informacion</div>
                <div style="margin-top:12px;font-size:12px;color:#f97316;font-weight:500">Ver perfil â†’</div>
            </div>
        </div>

    </div>
</div>
</template>

