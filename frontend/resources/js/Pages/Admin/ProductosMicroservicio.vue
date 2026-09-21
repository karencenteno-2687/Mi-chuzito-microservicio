<script setup>
import { router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const API_URL = 'http://localhost:8086/productos'

const productos = ref([])
const cargando = ref(true)
const error = ref('')

const mostrarForm = ref(false)
const editando = ref(null)

const form = ref({
    nombre: '',
    descripcion: '',
    precio: '',
    stock: '',
})

const cargarProductos = async () => {
    cargando.value = true
    error.value = ''
    try {
        const res = await fetch(API_URL)
        if (!res.ok) throw new Error('No se pudo conectar con el microservicio')
        productos.value = await res.json()
    } catch (e) {
        error.value = 'No se pudo conectar con el microservicio. Verifica que este corriendo en el puerto 8086.'
    } finally {
        cargando.value = false
    }
}

const abrirCrear = () => {
    editando.value = null
    form.value = { nombre: '', descripcion: '', precio: '', stock: '' }
    mostrarForm.value = true
}

const abrirEditar = (producto) => {
    editando.value = producto.id
    form.value = {
        nombre: producto.nombre,
        descripcion: producto.descripcion || '',
        precio: producto.precio,
        stock: producto.stock,
    }
    mostrarForm.value = true
}

const cerrarForm = () => {
    mostrarForm.value = false
    editando.value = null
}

const guardar = async () => {
    error.value = ''
    const payload = {
        nombre: form.value.nombre,
        descripcion: form.value.descripcion,
        precio: parseFloat(form.value.precio),
        stock: parseInt(form.value.stock),
    }

    try {
        const url = editando.value ? `${API_URL}/${editando.value}` : API_URL
        const method = editando.value ? 'PUT' : 'POST'

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        })

        if (!res.ok) {
            const data = await res.json().catch(() => null)
            throw new Error(data?.message || 'Error al guardar el producto')
        }

        cerrarForm()
        await cargarProductos()
    } catch (e) {
        error.value = e.message
    }
}

const eliminar = async (id) => {
    if (!confirm('¿Eliminar este producto del microservicio?')) return
    error.value = ''
    try {
        const res = await fetch(`${API_URL}/${id}`, { method: 'DELETE' })
        if (!res.ok) throw new Error('No se pudo eliminar el producto')
        await cargarProductos()
    } catch (e) {
        error.value = e.message
    }
}

const formatPrice = (v) => '$' + Number(v).toLocaleString('es-CO')

onMounted(cargarProductos)
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/dashboard" style="font-size:13px;color:#64748b;text-decoration:none">Volver al panel</a>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:900px;margin:2rem auto;padding:0 1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <div>
                <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Productos (Microservicio)</h1>
                <p style="font-size:13px;color:#64748b;margin:4px 0 0">Conectado a http://localhost:8086/productos</p>
            </div>
            <button @click="abrirCrear" style="padding:9px 18px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">+ Nuevo producto</button>
        </div>

        <div v-if="error" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:1.5rem">
            {{ error }}
        </div>

        <div v-if="mostrarForm" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem">
            <h2 style="font-size:15px;font-weight:700;color:#1e293b;margin:0 0 1rem">{{ editando ? 'Editar producto' : 'Nuevo producto' }}</h2>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">NOMBRE</label>
                    <input v-model="form.nombre" style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box"/>
                </div>
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">PRECIO</label>
                    <input v-model="form.precio" type="number" step="0.01" style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box"/>
                </div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">DESCRIPCION</label>
                <input v-model="form.descripcion" style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box"/>
            </div>

            <div style="margin-bottom:1.5rem;max-width:200px">
                <label style="font-size:11px;font-weight:600;color:#64748b;display:block;margin-bottom:4px">STOCK</label>
                <input v-model="form.stock" type="number" style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;box-sizing:border-box"/>
            </div>

            <div style="display:flex;gap:10px">
                <button @click="guardar" style="padding:9px 18px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Guardar</button>
                <button @click="cerrarForm" style="padding:9px 18px;background:#f1f5f9;color:#64748b;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Cancelar</button>
            </div>
        </div>

        <div v-if="cargando" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:3rem;text-align:center;color:#94a3b8">
            Cargando productos...
        </div>

        <div v-else style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <table style="width:100%;border-collapse:collapse;font-size:13px">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0">
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Nombre</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Descripcion</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Precio</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Stock</th>
                        <th style="padding:10px 16px;text-align:left;color:#64748b;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="producto in productos" :key="producto.id" style="border-bottom:1px solid #e2e8f0">
                        <td style="padding:10px 16px;font-weight:600;color:#1e293b">{{ producto.nombre }}</td>
                        <td style="padding:10px 16px;color:#64748b">{{ producto.descripcion }}</td>
                        <td style="padding:10px 16px;color:#64748b">{{ formatPrice(producto.precio) }}</td>
                        <td style="padding:10px 16px;color:#64748b">{{ producto.stock }}</td>
                        <td style="padding:10px 16px">
                            <button @click="abrirEditar(producto)" style="padding:5px 12px;background:#eff6ff;color:#1d4ed8;border:none;border-radius:6px;font-size:12px;cursor:pointer;margin-right:6px">Editar</button>
                            <button @click="eliminar(producto.id)" style="padding:5px 12px;background:#fef2f2;color:#dc2626;border:none;border-radius:6px;font-size:12px;cursor:pointer">Eliminar</button>
                        </td>
                    </tr>
                    <tr v-if="productos.length === 0">
                        <td colspan="5" style="padding:2rem;text-align:center;color:#94a3b8">No hay productos registrados en el microservicio</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</template>
