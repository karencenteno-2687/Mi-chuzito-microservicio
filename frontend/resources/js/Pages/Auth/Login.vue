<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const activeTab = ref('login')

const loginForm = useForm({
    login:    '',
    password: '',
    remember: false,
})

const registerForm = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
})

const showLoginPassword = ref(false)
const showRegisterPassword = ref(false)
const registerMethod = ref('email')

const submitLogin = () => loginForm.post('/login')
const submitRegister = () => registerForm.post('/register')
</script>

<template>
<div style="min-height:100vh;display:flex;font-family:'Segoe UI',sans-serif">

    <!-- Panel izquierdo -->
    <div style="width:380px;min-height:100vh;background:linear-gradient(135deg,#f97316,#ea580c);display:flex;flex-direction:column;justify-content:center;padding:3rem 2.5rem;position:relative;overflow:hidden;flex-shrink:0">
        <div style="position:absolute;top:-60px;left:-60px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,0.08)"></div>
        <div style="position:absolute;bottom:-80px;right:-40px;width:250px;height:250px;border-radius:50%;background:rgba(255,255,255,0.06)"></div>
        <div style="position:relative;z-index:1">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:3rem">
                <div style="width:40px;height:40px;background:rgba(255,255,255,0.2);border-radius:10px;display:flex;align-items:center;justify-content:center">
                    <svg width="22" height="22" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                </div>
                <span style="color:white;font-size:20px;font-weight:700">Mi Chuzito</span>
            </div>
            <h1 style="color:white;font-size:42px;font-weight:800;line-height:1.1;margin-bottom:1rem">Tu sabor,<br>tu<br>plataforma.</h1>
            <p style="color:rgba(255,255,255,0.85);font-size:14px;line-height:1.6;margin-bottom:2rem">Gestiona tu negocio, tus clientes y tus ventas desde un solo lugar seguro y eficiente.</p>
            <ul style="list-style:none;padding:0;margin:0 0 2rem">
                <li style="color:rgba(255,255,255,0.9);font-size:13px;margin-bottom:10px;display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:white;display:inline-block;flex-shrink:0"></span>
                    Registro por correo o telefono
                </li>
                <li style="color:rgba(255,255,255,0.9);font-size:13px;margin-bottom:10px;display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:white;display:inline-block;flex-shrink:0"></span>
                    Verificacion en dos pasos (2FA)
                </li>
                <li style="color:rgba(255,255,255,0.9);font-size:13px;display:flex;align-items:center;gap:8px">
                    <span style="width:6px;height:6px;border-radius:50%;background:white;display:inline-block;flex-shrink:0"></span>
                    Acceso controlado por rol
                </li>
            </ul>
            <button @click="router.visit('/')" style="background:rgba(255,255,255,0.2);border:1.5px solid rgba(255,255,255,0.4);color:white;padding:9px 18px;border-radius:8px;font-size:13px;cursor:pointer;font-weight:500">
                Ver menu
            </button>
        </div>
    </div>

    <!-- Panel derecho -->
    <div style="flex:1;display:flex;align-items:center;justify-content:center;background:white;padding:2rem;overflow-y:auto">
        <div style="width:100%;max-width:480px">

            <!-- Tabs -->
            <div style="display:flex;background:#f1f5f9;border-radius:10px;padding:4px;margin-bottom:2rem">
                <button @click="activeTab='login'"
                    :style="`flex:1;padding:10px;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.2s;${activeTab==='login' ? 'background:white;color:#f97316;box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'background:transparent;color:#94a3b8'}`">
                    Iniciar sesion
                </button>
                <button @click="activeTab='register'"
                    :style="`flex:1;padding:10px;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.2s;${activeTab==='register' ? 'background:white;color:#f97316;box-shadow:0 1px 4px rgba(0,0,0,0.1)' : 'background:transparent;color:#94a3b8'}`">
                    Registrarse
                </button>
            </div>

            <!-- LOGIN -->
            <div v-if="activeTab==='login'">
                <h2 style="font-size:24px;font-weight:700;color:#1e293b;margin-bottom:4px">Bienvenido de nuevo</h2>
                <p style="font-size:13px;color:#94a3b8;margin-bottom:1.5rem">Accede con tu correo o numero de telefono</p>

                <div v-if="loginForm.errors.login" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:1rem">
                    {{ loginForm.errors.login }}
                </div>

                <div style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Correo o Telefono</label>
                    <div style="position:relative;margin-top:6px">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%)" width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <input v-model="loginForm.login" type="text" placeholder="correo@ejemplo.com o +57 300 000 0000"
                            style="width:100%;padding:11px 12px 11px 38px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                            @focus="$event.target.style.borderColor='#f97316'"
                            @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Contrasena</label>
                    <div style="position:relative;margin-top:6px">
                        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%)" width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        <input v-model="loginForm.password" :type="showLoginPassword ? 'text' : 'password'" placeholder="Ingresa tu contrasena"
                            style="width:100%;padding:11px 40px 11px 38px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                            @focus="$event.target.style.borderColor='#f97316'"
                            @blur="$event.target.style.borderColor='#e2e8f0'"/>
                        <button @click="showLoginPassword=!showLoginPassword" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer">
                            <svg width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24">
                                <path v-if="!showLoginPassword" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle v-if="!showLoginPassword" cx="12" cy="12" r="3"/>
                                <path v-if="showLoginPassword" d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line v-if="showLoginPassword" x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
                    <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#64748b;cursor:pointer">
                        <input v-model="loginForm.remember" type="checkbox" style="accent-color:#f97316"/> Recordar sesion
                    </label>
                    <a href="/forgot-password" style="font-size:13px;color:#f97316;text-decoration:none;font-weight:500">Olvide mi contrasena</a>
                </div>

                <button @click="submitLogin" :disabled="loginForm.processing"
                    style="width:100%;padding:13px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer">
                    {{ loginForm.processing ? 'Ingresando...' : 'Ingresar' }}
                </button>
            </div>

            <!-- REGISTRO -->
            <div v-if="activeTab==='register'">
                <h2 style="font-size:24px;font-weight:700;color:#1e293b;margin-bottom:4px">Crea tu cuenta</h2>
                <p style="font-size:13px;color:#94a3b8;margin-bottom:1.5rem">Unete a Mi Chuzito hoy mismo</p>

                <div style="display:flex;gap:8px;margin-bottom:1.5rem">
                    <button @click="registerMethod='email'"
                        :style="`flex:1;padding:10px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:1.5px solid;${registerMethod==='email' ? 'background:#fff7ed;border-color:#f97316;color:#f97316' : 'background:white;border-color:#e2e8f0;color:#94a3b8'}`">
                        Correo electronico
                    </button>
                    <button @click="registerMethod='phone'"
                        :style="`flex:1;padding:10px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:1.5px solid;${registerMethod==='phone' ? 'background:#fff7ed;border-color:#f97316;color:#f97316' : 'background:white;border-color:#e2e8f0;color:#94a3b8'}`">
                        Numero de telefono
                    </button>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Nombre completo</label>
                    <input v-model="registerForm.name" placeholder="Solo letras, sin espacios ni caracteres especiales"
                        style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="registerForm.errors.name" style="color:#dc2626;font-size:11px;margin-top:4px;display:block">{{ registerForm.errors.name }}</span>
                    <span v-else style="font-size:11px;color:#94a3b8;margin-top:4px;display:block">Solo letras A-Z, sin espacios ni tildes</span>
                </div>

                <div v-if="registerMethod==='email'" style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Correo Electronico</label>
                    <input v-model="registerForm.email" type="email" placeholder="correo@ejemplo.com"
                        style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="registerForm.errors.email" style="color:#dc2626;font-size:11px">{{ registerForm.errors.email }}</span>
                </div>

                <div v-if="registerMethod==='phone'" style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Telefono</label>
                    <input v-model="registerForm.phone" placeholder="+57 300 000 0000"
                        style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:1.5rem">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Contrasena</label>
                        <div style="position:relative;margin-top:6px">
                            <input v-model="registerForm.password" :type="showRegisterPassword ? 'text' : 'password'" placeholder="Min. 8 caracteres"
                                style="width:100%;padding:11px 36px 11px 11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box"
                                @focus="$event.target.style.borderColor='#f97316'"
                                @blur="$event.target.style.borderColor='#e2e8f0'"/>
                            <button @click="showRegisterPassword=!showRegisterPassword" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer">
                                <svg width="15" height="15" fill="none" stroke="#94a3b8" stroke-width="2" viewBox="0 0 24 24">
                                    <path v-if="!showRegisterPassword" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle v-if="!showRegisterPassword" cx="12" cy="12" r="3"/>
                                    <path v-if="showRegisterPassword" d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><line v-if="showRegisterPassword" x1="1" y1="1" x2="23" y2="23"/>
                                </svg>
                            </button>
                        </div>
                        <span v-if="registerForm.errors.password" style="color:#dc2626;font-size:11px">{{ registerForm.errors.password }}</span>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;letter-spacing:0.05em;text-transform:uppercase">Confirmar</label>
                        <input v-model="registerForm.password_confirmation" type="password" placeholder="Repite la contrasena"
                            style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:6px"
                            @focus="$event.target.style.borderColor='#f97316'"
                            @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                </div>

                <label style="display:flex;align-items:flex-start;gap:8px;font-size:12px;color:#64748b;margin-bottom:1.5rem;cursor:pointer">
                    <input type="checkbox" style="margin-top:2px;accent-color:#f97316"/>
                    <span>Acepto los <a href="#" style="color:#f97316;text-decoration:none">terminos y condiciones</a> y la <a href="#" style="color:#f97316;text-decoration:none">politica de privacidad</a></span>
                </label>

                <button @click="submitRegister" :disabled="registerForm.processing"
                    style="width:100%;padding:13px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer">
                    {{ registerForm.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                </button>
            </div>

        </div>
    </div>
</div>
</template>