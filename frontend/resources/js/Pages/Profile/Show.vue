<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const { user } = defineProps(['user'])
const activeTab = ref('info')

const infoForm = useForm({
    name:            user.name ?? '',
    phone:           user.phone ?? '',
    address:         user.profile?.address ?? '',
    city:            user.profile?.city ?? '',
    department:      user.profile?.department ?? '',
    document_type:   user.profile?.document_type ?? '',
    document_number: user.profile?.document_number ?? '',
    birth_date:      user.profile?.birth_date ?? '',
})

const passwordForm = useForm({
    current_password: '',
    password:         '',
    password_confirmation: '',
})

const submitInfo = () => infoForm.put('/perfil')
const submitPassword = () => passwordForm.put('/perfil/contrasena')

const logout = () => router.post('/logout')

const role = usePage().props.auth.user.roles?.[0] ?? 'sin rol'
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
            <span style="font-size:13px;padding:3px 10px;border-radius:20px;background:#fff7ed;color:#f97316;font-weight:500">{{ role }}</span>
            <button @click="logout" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:900px;margin:2rem auto;padding:0 1rem">

        <!-- Encabezado perfil -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:2rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.5rem">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;align-items:center;justify-content:center;font-size:28px;color:white;font-weight:700;flex-shrink:0">
                {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#1e293b">{{ user.name }}</div>
                <div style="font-size:14px;color:#64748b;margin-top:2px">{{ user.email }}</div>
                <span style="display:inline-block;margin-top:6px;font-size:12px;padding:2px 10px;border-radius:20px;background:#fff7ed;color:#f97316;font-weight:500">{{ role }}</span>
            </div>
        </div>

        <!-- Tabs -->
        <div style="display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:4px;margin-bottom:1.5rem;width:fit-content">
            <button @click="activeTab='info'"
                :style="`padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;${activeTab==='info' ? 'background:white;color:#f97316;box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'background:transparent;color:#94a3b8'}`">
                Informacion personal
            </button>
            <button @click="activeTab='password'"
                :style="`padding:8px 20px;border:none;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;${activeTab==='password' ? 'background:white;color:#f97316;box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'background:transparent;color:#94a3b8'}`">
                Contrasena
            </button>
        </div>

        <!-- Informacion personal -->
        <div v-if="activeTab==='info'" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:2rem">
            <h2 style="font-size:17px;font-weight:600;color:#1e293b;margin-bottom:1.5rem">Informacion personal</h2>

            <div v-if="infoForm.recentlySuccessful" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
                Perfil actualizado correctamente.
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Nombre completo</label>
                    <input v-model="infoForm.name"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="infoForm.errors.name" style="color:#dc2626;font-size:11px">{{ infoForm.errors.name }}</span>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Telefono</label>
                    <input v-model="infoForm.phone"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Direccion</label>
                <input v-model="infoForm.address"
                    style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                    @focus="$event.target.style.borderColor='#f97316'"
                    @blur="$event.target.style.borderColor='#e2e8f0'"/>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Ciudad</label>
                    <input v-model="infoForm.city"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Departamento</label>
                    <input v-model="infoForm.department"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Tipo documento</label>
                    <select v-model="infoForm.document_type"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px;background:white">
                        <option value="">Seleccionar</option>
                        <option value="CC">Cedula de ciudadania</option>
                        <option value="TI">Tarjeta de identidad</option>
                        <option value="CE">Cedula de extranjeria</option>
                        <option value="PP">Pasaporte</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Numero documento</label>
                    <input v-model="infoForm.document_number"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Fecha nacimiento</label>
                    <input v-model="infoForm.birth_date" type="date"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>
            </div>

            <button @click="submitInfo" :disabled="infoForm.processing"
                style="padding:11px 28px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
                {{ infoForm.processing ? 'Guardando...' : 'Guardar cambios' }}
            </button>
        </div>

        <!-- Cambio de contrasena -->
        <div v-if="activeTab==='password'" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:2rem">
            <h2 style="font-size:17px;font-weight:600;color:#1e293b;margin-bottom:1.5rem">Cambiar contrasena</h2>

            <div v-if="passwordForm.recentlySuccessful" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
                Contrasena actualizada correctamente.
            </div>

            <div style="max-width:400px">
                <div style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Contrasena actual</label>
                    <input v-model="passwordForm.current_password" type="password"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="passwordForm.errors.current_password" style="color:#dc2626;font-size:11px">{{ passwordForm.errors.current_password }}</span>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Nueva contrasena</label>
                    <input v-model="passwordForm.password" type="password"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="passwordForm.errors.password" style="color:#dc2626;font-size:11px">{{ passwordForm.errors.password }}</span>
                </div>

                <div style="margin-bottom:1.5rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Confirmar nueva contrasena</label>
                    <input v-model="passwordForm.password_confirmation" type="password"
                        style="width:100%;padding:10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>

                <button @click="submitPassword" :disabled="passwordForm.processing"
                    style="padding:11px 28px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">
                    {{ passwordForm.processing ? 'Actualizando...' : 'Actualizar contrasena' }}
                </button>
            </div>
        </div>

    </div>
</div>
</template>