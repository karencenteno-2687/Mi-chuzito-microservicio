<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3'

const { users } = defineProps(['users'])
const logout = () => router.post('/logout')
const role = usePage().props.auth.user.roles?.[0] ?? 'sin rol'

const changeRole = (id, newRole) => {
    router.patch(`/admin/users/${id}/role`, { role: newRole })
}

const deactivate = (id) => {
    if (confirm('Desactivar este usuario?')) {
        router.patch(`/admin/users/${id}/deactivate`)
    }
}

const createForm = useForm({
    name:  '',
    email: '',
    role:  'cliente',
})

const showCreate = useForm({ visible: false })
const submitCreate = () => createForm.post('/admin/users')
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <!-- Navbar -->
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
            <span style="font-size:13px;padding:3px 10px;border-radius:20px;background:#f3e8ff;color:#7c3aed;font-weight:500">{{ role }}</span>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1100px;margin:2rem auto;padding:0 1rem">

        <!-- Encabezado -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <div>
                <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Gestion de usuarios</h1>
                <p style="font-size:13px;color:#64748b;margin:4px 0 0">Administra cuentas, roles y permisos</p>
            </div>
            <button @click="showCreate.visible = !showCreate.visible"
                style="padding:10px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                + Nuevo usuario
            </button>
        </div>

        <!-- Formulario crear usuario -->
        <div v-if="showCreate.visible" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem">
            <h3 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:1rem">Crear nuevo usuario</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:10px;align-items:end">
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Nombre</label>
                    <input v-model="createForm.name" placeholder="Nombre completo"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="createForm.errors.name" style="color:#dc2626;font-size:11px">{{ createForm.errors.name }}</span>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Correo</label>
                    <input v-model="createForm.email" type="email" placeholder="correo@ejemplo.com"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="createForm.errors.email" style="color:#dc2626;font-size:11px">{{ createForm.errors.email }}</span>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Rol</label>
                    <select v-model="createForm.role"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:6px;background:white">
                        <option value="cliente">Cliente</option>
                        <option value="vendedor">Vendedor</option>
                        <option value="repartidor">Repartidor</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>
                <button @click="submitCreate" :disabled="createForm.processing"
                    style="padding:10px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap">
                    Crear
                </button>
            </div>
            <p style="font-size:12px;color:#94a3b8;margin-top:8px">La contrasena temporal sera: Temporal.123</p>
        </div>

        <!-- Tabla de usuarios -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0">
                <span style="font-size:14px;font-weight:600;color:#1e293b">Total: {{ users.total }} usuarios</span>
            </div>
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Usuario</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Contacto</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Rol</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Estado</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="u in users.data" :key="u.id" style="border-top:1px solid #f1f5f9">
                        <td style="padding:14px 16px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;color:white;font-weight:600;font-size:14px;flex-shrink:0">
                                    {{ u.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div style="font-size:14px;font-weight:500;color:#1e293b">{{ u.name }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:14px 16px">
                            <div style="font-size:13px;color:#64748b">{{ u.email }}</div>
                            <div style="font-size:12px;color:#94a3b8">{{ u.phone ?? 'Sin telefono' }}</div>
                        </td>
                        <td style="padding:14px 16px">
                            <select @change="changeRole(u.id, $event.target.value)"
                                :style="`padding:5px 8px;border:1.5px solid #e2e8f0;border-radius:6px;font-size:12px;outline:none;background:white;cursor:pointer`">
                                <option v-for="r in ['cliente','vendedor','repartidor','administrador']"
                                    :key="r" :value="r" :selected="u.roles[0]?.name === r">
                                    {{ r }}
                                </option>
                            </select>
                        </td>
                        <td style="padding:14px 16px">
                            <span :style="`font-size:12px;padding:3px 10px;border-radius:20px;font-weight:500;${u.is_active ? 'background:#f0fdf4;color:#15803d' : 'background:#fef2f2;color:#dc2626'}`">
                                {{ u.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td style="padding:14px 16px">
                            <button v-if="u.is_active" @click="deactivate(u.id)"
                                style="padding:5px 12px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                Desactivar
                            </button>
                            <span v-else style="font-size:12px;color:#94a3b8">Inactivo</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginacion -->
            <div style="padding:1rem 1.5rem;border-top:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:13px;color:#64748b">Pagina {{ users.current_page }} de {{ users.last_page }}</span>
                <div style="display:flex;gap:6px">
                    <button v-if="users.prev_page_url" @click="router.visit(users.prev_page_url)"
                        style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;background:white;font-size:13px;cursor:pointer">
                        Anterior
                    </button>
                    <button v-if="users.next_page_url" @click="router.visit(users.next_page_url)"
                        style="padding:6px 12px;border:1px solid #e2e8f0;border-radius:6px;background:white;font-size:13px;cursor:pointer">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
</template>