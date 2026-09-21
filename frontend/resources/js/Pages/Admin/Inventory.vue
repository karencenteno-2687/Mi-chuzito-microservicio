<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps([
    'products',
    'supplies',
    'agotados',
    'bajos',
    'insumosBajos'
])

const {
    products,
    supplies,
    agotados,
    bajos,
    insumosBajos
} = props

const defaultSupplyCategories = [
    'Carnes',
    'Tubérculos',
    'Panadería',
    'Lácteos',
    'Salsas',
    'Verduras',
    'Bebidas',
    'Condimentos',
    'Empaques y Desechables',
    'Abarrotes',
    'Otros'
]

const defaultUnits = [
    'Kg',
    'g',
    'Litros',
    'ml',
    'Unidades',
    'Paquetes',
    'Porciones',
    'Cajas',
    'Latas',
    'Botellas'
]

const availableCategories = computed(() => {
    const existing = (props.supplies || [])
        .map(s => s.category)
        .filter(c => Boolean(c) && c !== 'Otros')
    const defaultsWithoutOtros = defaultSupplyCategories.filter(c => c !== 'Otros')
    return [...Array.from(new Set([...defaultsWithoutOtros, ...existing])), 'Otros']
})

const availableUnits = computed(() => {
    const existing = (props.supplies || []).map(s => s.unit).filter(Boolean)
    return Array.from(new Set([...defaultUnits, ...existing]))
})
const editForm   = useForm({ stock: 0, is_available: true, price: 0, image: null })
const createForm = useForm({ name: '', description: '', price: '', category: '', stock: '', image: null })
const supplyForm = useForm({
    name: '',
    category: '',
    unit: '',
    cost: '',
    stock: '',
    minimum_stock: '',
})
const createSupplyForm = useForm({
    name: '',
    category: '',
    unit: 'Kg',
    cost: '',
    stock: '',
    minimum_stock: '5',
})
const editingSupply = ref(null)
const showCreateSupply = ref(false)
const formattedCreateCost = ref('')
const formattedEditCost = ref('')
const selectedCreateCategory = ref('')
const customCreateCategory = ref('')
const selectedEditCategory = ref('')
const customEditCategory = ref('')

const openCategoryDropdown = ref(false)
const openUnitDropdown = ref(false)

const toggleCategoryDropdown = () => {
    openCategoryDropdown.value = !openCategoryDropdown.value
    if (openCategoryDropdown.value) {
        openUnitDropdown.value = false
    }
}

const toggleUnitDropdown = () => {
    openUnitDropdown.value = !openUnitDropdown.value
    if (openUnitDropdown.value) {
        openCategoryDropdown.value = false
    }
}

const selectCategory = (cat) => {
    selectedCreateCategory.value = cat
    onCategorySelectChange()
    openCategoryDropdown.value = false
}

const selectUnit = (u) => {
    createSupplyForm.unit = u
    openUnitDropdown.value = false
}

const closeDropdowns = (e) => {
    if (!e.target.closest('.custom-select-category')) {
        openCategoryDropdown.value = false
    }
    if (!e.target.closest('.custom-select-unit')) {
        openUnitDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', closeDropdowns)
})

onUnmounted(() => {
    document.removeEventListener('click', closeDropdowns)
})

const onCategorySelectChange = () => {
    if (selectedCreateCategory.value === 'Otros') {
        createSupplyForm.category = customCreateCategory.value
    } else {
        createSupplyForm.category = selectedCreateCategory.value
        customCreateCategory.value = ''
    }
}

const onCustomCategoryInput = (e) => {
    customCreateCategory.value = e.target.value
    if (selectedCreateCategory.value === 'Otros') {
        createSupplyForm.category = e.target.value
    }
}

const onEditCategorySelectChange = () => {
    if (selectedEditCategory.value === 'Otros') {
        supplyForm.category = customEditCategory.value
    } else {
        supplyForm.category = selectedEditCategory.value
        customEditCategory.value = ''
    }
}

const onCustomEditCategoryInput = (e) => {
    customEditCategory.value = e.target.value
    if (selectedEditCategory.value === 'Otros') {
        supplyForm.category = e.target.value
    }
}

const formatThousands = (val) => {
    if (!val && val !== 0) return ''
    const clean = val.toString().replace(/\D/g, '')
    if (!clean) return ''
    return clean.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

const onCostCreateInput = (e) => {
    const rawDigits = e.target.value.replace(/\D/g, '')
    if (!rawDigits) {
        createSupplyForm.cost = ''
        formattedCreateCost.value = ''
        return
    }
    const num = parseInt(rawDigits, 10)
    createSupplyForm.cost = num
    formattedCreateCost.value = formatThousands(num)
}

const onCostEditInput = (e) => {
    const rawDigits = e.target.value.replace(/\D/g, '')
    if (!rawDigits) {
        supplyForm.cost = ''
        formattedEditCost.value = ''
        return
    }
    const num = parseInt(rawDigits, 10)
    supplyForm.cost = num
    formattedEditCost.value = formatThousands(num)
}

const saveSupply = (id) => {
    if (selectedEditCategory.value === 'Otros') {
        supplyForm.category = customEditCategory.value.trim()
    } else {
        supplyForm.category = selectedEditCategory.value
    }
    supplyForm.post(`/admin/supplies/${id}`, {
        onSuccess: () => {
            editingSupply.value = null
            supplyForm.reset()
            formattedEditCost.value = ''
            selectedEditCategory.value = ''
            customEditCategory.value = ''
        }
    })
}

const submitCreateSupply = () => {
    if (selectedCreateCategory.value === 'Otros') {
        createSupplyForm.category = customCreateCategory.value.trim()
    } else {
        createSupplyForm.category = selectedCreateCategory.value
    }
    createSupplyForm.post('/admin/supplies', {
        onSuccess: () => {
            showCreateSupply.value = false
            createSupplyForm.reset()
            formattedCreateCost.value = ''
            selectedCreateCategory.value = ''
            customCreateCategory.value = ''
            openCategoryDropdown.value = false
            openUnitDropdown.value = false
        }
    })
}

const cancelCreateSupply = () => {
    showCreateSupply.value = false
    createSupplyForm.reset()
    formattedCreateCost.value = ''
    selectedCreateCategory.value = ''
    customCreateCategory.value = ''
    openCategoryDropdown.value = false
    openUnitDropdown.value = false
}

const cancelEditSupply = () => {
    editingSupply.value = null
    supplyForm.reset()
    formattedEditCost.value = ''
    selectedEditCategory.value = ''
    customEditCategory.value = ''
}
const editingId   = ref(null)
const showCreate  = ref(false)
const createPreview = ref(null)
const editPreview   = ref(null)

const startEdit = (product) => {
    editingId.value = product.id
    editForm.stock        = product.stock
    editForm.is_available = product.is_available
    editForm.price        = product.price
    editForm.image        = null
    editPreview.value     = null
}

const onCreateImageChange = (e) => {
    const file = e.target.files[0]
    createForm.image = file
    if (file) createPreview.value = URL.createObjectURL(file)
}

const onEditImageChange = (e) => {
    const file = e.target.files[0]
    editForm.image = file
    if (file) editPreview.value = URL.createObjectURL(file)
}

const saveEdit = (productId) => {
    editForm.post(`/admin/inventory/${productId}`, {
        forceFormData: true,
        onSuccess: () => { editingId.value = null; editPreview.value = null }
    })
}

const deleteProduct = (productId) => {
    if (confirm('Eliminar este producto?')) {
        router.delete(`/admin/inventory/${productId}`)
    }
}

const submitCreate = () => {
    createForm.post('/admin/inventory', {
        forceFormData: true,
        onSuccess: () => { showCreate.value = false; createForm.reset(); createPreview.value = null }
    })
}

const formatPrice = (p) => {
    const num = Math.round(Number(p) || 0)
    return '$' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
}

const startEditSupply = (supply) => {
    editingSupply.value = supply.id
    supplyForm.clearErrors()
    supplyForm.name = supply.name
    selectedEditCategory.value = supply.category
    supplyForm.category = supply.category
    customEditCategory.value = ''
    supplyForm.unit = supply.unit
    const costNum = Math.round(Number(supply.cost)) || 0
    supplyForm.cost = costNum
    formattedEditCost.value = costNum > 0 ? formatThousands(costNum) : ''
    supplyForm.stock = Math.round(Number(supply.stock)) || 0
    supplyForm.minimum_stock = Math.round(Number(supply.minimum_stock)) || 0
}
const deleteSupply = (id) => {
    if (confirm('¿Eliminar este insumo?')) {
        router.delete(`/admin/supplies/${id}`)
    }

}
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif">

    <nav style="background:white;border-bottom:1px solid #e2e8f0;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;height:64px;position:sticky;top:0;z-index:100">
        <div style="display:flex;align-items:center;gap:10px;cursor:pointer" @click="router.visit('/dashboard')">
            <div style="width:36px;height:36px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span style="font-size:17px;font-weight:700;color:#1e293b">Mi Chuzito</span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <a href="/admin/users" style="font-size:13px;color:#64748b;text-decoration:none">Usuarios</a>
            <a href="/admin/orders" style="font-size:13px;color:#64748b;text-decoration:none">Pedidos</a>
            <a href="/admin/payments" style="font-size:13px;color:#64748b;text-decoration:none">Pagos</a>
            <span style="font-size:12px;padding:3px 10px;border-radius:20px;background:#f3e8ff;color:#7c3aed;font-weight:500">Administrador</span>
            <button @click="router.post('/logout')" style="padding:6px 14px;background:#ef4444;color:white;border:none;border-radius:6px;cursor:pointer;font-size:13px">Cerrar sesion</button>
        </div>
    </nav>

    <div style="max-width:1100px;margin:2rem auto;padding:0 1rem"> 

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
            <div>
                <h1 style="font-size:22px;font-weight:700;color:#1e293b;margin:0">Gestion de inventario</h1>
                <p style="font-size:13px;color:#64748b;margin:4px 0 0">Administra productos, precios, stock e imagenes</p>
            </div>
            <button @click="showCreate=!showCreate"
                style="padding:10px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                + Nuevo producto
            </button>
        </div>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">            <div style="background:white;border-radius:10px;border:1px solid #e2e8f0;padding:1rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#f97316">{{ products.length }}</div>
                <div style="font-size:12px;color:#64748b">Total productos</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #fef2f2;padding:1rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#dc2626">{{ agotados }}</div>
                <div style="font-size:12px;color:#64748b">Agotados</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #fff7ed;padding:1rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#c2410c">{{ bajos }}</div>
                <div style="font-size:12px;color:#64748b">Stock bajo (5 o menos)</div>
            </div>
            <div style="background:white;border-radius:10px;border:1px solid #dcfce7;padding:1rem;text-align:center">
                <div style="font-size:28px;font-weight:800;color:#16a34a">
                {{ insumosBajos }}
            </div>

            <div style="font-size:12px;color:#64748b">Insumos con stock bajo</div>

</div>
        </div>

        <!-- Formulario crear producto -->
        <div v-if="showCreate" style="background:white;border-radius:12px;border:1px solid #e2e8f0;padding:1.5rem;margin-bottom:1.5rem">
            <h3 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:1rem">Nuevo producto</h3>

            <div style="display:flex;gap:1.5rem;margin-bottom:1rem">
                <!-- Vista previa imagen -->
                <div style="flex-shrink:0">
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Imagen</label>
                    <div style="margin-top:4px;width:120px;height:120px;border-radius:10px;border:2px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;overflow:hidden;background:#f8fafc;position:relative">
                        <img v-if="createPreview" :src="createPreview" style="width:100%;height:100%;object-fit:cover"/>
                        <svg v-else width="32" height="32" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" @change="onCreateImageChange"
                            style="position:absolute;inset:0;opacity:0;cursor:pointer"/>
                    </div>
                    <span v-if="createForm.errors.image" style="color:#dc2626;font-size:11px;display:block;margin-top:4px;max-width:120px">{{ createForm.errors.image }}</span>
                </div>

                <div style="flex:1;display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Nombre</label>
                        <input v-model="createForm.name" placeholder="Nombre del producto"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Categoria</label>
                        <input v-model="createForm.category" placeholder="Ej: Chuzo de Res"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Precio</label>
                        <input v-model="createForm.price" type="number" placeholder="0"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Stock inicial</label>
                        <input v-model="createForm.stock" type="number" placeholder="0"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                    <div style="grid-column:span 2">
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Descripcion</label>
                        <input v-model="createForm.description" placeholder="Descripcion del producto"
                            style="width:100%;padding:9px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'" @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:8px">
                <button @click="submitCreate" :disabled="createForm.processing"
                    style="padding:9px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                    {{ createForm.processing ? 'Subiendo...' : 'Crear producto' }}
                </button>
                <button @click="showCreate=false;createForm.reset();createPreview=null"
                    style="padding:9px 20px;background:#f1f5f9;color:#64748b;border:none;border-radius:8px;font-size:13px;cursor:pointer">
                    Cancelar
                </button>
            </div>
        </div>

        <!-- Tabla inventario -->
        <div style="background:white;border-radius:12px;border:1px solid #e2e8f0;overflow:hidden">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Producto</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Categoria</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Precio</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Stock</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Estado</th>
                        <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="product in products" :key="product.id" style="border-top:1px solid #f1f5f9">
                        <td style="padding:12px 16px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="position:relative;width:44px;height:44px;border-radius:8px;overflow:hidden;flex-shrink:0;background:#f1f5f9">
                                    <img :src="editingId === product.id && editPreview ? editPreview : product.image_url_full" :alt="product.name" style="width:100%;height:100%;object-fit:cover"/>
                                    <input v-if="editingId === product.id" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" @change="onEditImageChange"
                                        style="position:absolute;inset:0;opacity:0;cursor:pointer"/>
                                </div>
                                <div>
                                    <div style="font-size:14px;font-weight:500;color:#1e293b">{{ product.name }}</div>
                                    <div style="font-size:11px;color:#94a3b8">{{ product.description?.slice(0,40) }}...</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:12px 16px;font-size:13px;color:#64748b">{{ product.category }}</td>
                        <td style="padding:12px 16px;text-align:center">
                            <template v-if="editingId === product.id">
                                <input v-model="editForm.price" type="number"
                                    style="width:90px;padding:5px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:center;outline:none"/>
                            </template>
                            <span v-else style="font-size:13px;font-weight:500;color:#1e293b">{{ formatPrice(product.price) }}</span>
                        </td>
                        <td style="padding:12px 16px;text-align:center">
                            <template v-if="editingId === product.id">
                                <input v-model="editForm.stock" type="number" min="0"
                                    style="width:70px;padding:5px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:center;outline:none"/>
                            </template>
                            <span v-else :style="`font-size:14px;font-weight:700;${product.stock === 0 ? 'color:#dc2626' : product.stock <= 5 ? 'color:#c2410c' : 'color:#15803d'}`">
                                {{ product.stock }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center">
                            <template v-if="editingId === product.id">
                                <select v-model="editForm.is_available"
                                    style="padding:5px;border:1.5px solid #f97316;border-radius:6px;font-size:12px;outline:none;background:white">
                                    <option :value="true">Disponible</option>
                                    <option :value="false">No disponible</option>
                                </select>
                            </template>
                            <span v-else :style="`font-size:12px;padding:3px 10px;border-radius:20px;font-weight:500;${product.is_available ? 'background:#f0fdf4;color:#15803d' : 'background:#fef2f2;color:#dc2626'}`">
                                {{ product.is_available ? 'Disponible' : 'Agotado' }}
                            </span>
                        </td>
                        <td style="padding:12px 16px;text-align:center">
                            <div style="display:flex;gap:6px;justify-content:center">
                                <template v-if="editingId === product.id">
                                    <button @click="saveEdit(product.id)"
                                        style="padding:5px 12px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                        Guardar
                                    </button>
                                    <button @click="editingId=null;editPreview=null"
                                        style="padding:5px 12px;background:#f1f5f9;color:#64748b;border:none;border-radius:6px;font-size:12px;cursor:pointer">
                                        Cancelar
                                    </button>
                                </template>
                                <template v-else>
                                    <button @click="startEdit(product)"
                                        style="padding:5px 12px;background:#fff7ed;color:#f97316;border:1px solid #fed7aa;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                        Editar
                                    </button>
                                    <button @click="deleteProduct(product.id)"
                                        style="padding:5px 12px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500">
                                        Eliminar
                                    </button>
                                </template>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ===================== INSUMOS ===================== -->
        <div
            style="
                margin-top:35px;
                background:white;
                border-radius:12px;
                border:1px solid #e2e8f0;
                overflow:hidden;
                box-shadow:0 1px 3px rgba(0,0,0,.05);
            ">

            <!-- Encabezado -->
            <div
                style="
                    padding:18px 20px;
                    border-bottom:1px solid #e2e8f0;
                    background:#f8fafc;
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                ">
                <div>
                    <h2 style="margin:0;font-size:18px;font-weight:700;color:#1e293b">
                        Inventario de Insumos
                    </h2>
                    <p style="margin-top:4px;font-size:13px;color:#64748b">
                        Organiza y controla los insumos del negocio.
                    </p>
                </div>
                <button
                    @click="showCreateSupply ? cancelCreateSupply() : showCreateSupply = true"
                    style="
                        padding:9px 18px;
                        background:linear-gradient(135deg,#f97316,#ea580c);
                        color:white;
                        border:none;
                        border-radius:8px;
                        font-size:13px;
                        font-weight:600;
                        cursor:pointer;
                    ">
                    {{ showCreateSupply ? '✕ Cerrar formulario' : '+ Nuevo insumo' }}
                </button>
            </div>

            <!-- Formulario Crear Insumo -->
            <div v-if="showCreateSupply" style="padding:1.5rem;background:#fffaf0;border-bottom:1px solid #fed7aa">
                <h3 style="font-size:15px;font-weight:600;color:#9a3412;margin:0 0 1rem 0">Registrar nuevo insumo</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(160px, 1fr));gap:12px;margin-bottom:1rem">
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Nombre</label>
                        <input v-model="createSupplyForm.name" placeholder="Ej: Carne molida de res"
                            style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px" />
                        <span v-if="createSupplyForm.errors.name" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ createSupplyForm.errors.name }}</span>
                    </div>
                    <div class="custom-select-category" :style="openCategoryDropdown ? 'position:relative;z-index:40' : 'position:relative;z-index:10'">
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Categoría</label>
                        <div class="custom-dropdown-container">
                            <button
                                type="button"
                                class="custom-dropdown-btn"
                                :class="{ 'is-active': openCategoryDropdown }"
                                @click="toggleCategoryDropdown"
                            >
                                <span :class="{ 'placeholder': !selectedCreateCategory }">
                                    {{ selectedCreateCategory || 'Selecciona una categoría' }}
                                </span>
                                <svg class="custom-dropdown-arrow" :class="{ 'is-rotated': openCategoryDropdown }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <transition name="dropdown-anim">
                                <ul v-if="openCategoryDropdown" class="custom-dropdown-menu">
                                    <li
                                        v-for="cat in availableCategories"
                                        :key="cat"
                                        class="custom-dropdown-item"
                                        :class="{ 'is-selected': selectedCreateCategory === cat }"
                                        @click="selectCategory(cat)"
                                    >
                                        <span>{{ cat }}</span>
                                        <svg v-if="selectedCreateCategory === cat" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </li>
                                </ul>
                            </transition>
                        </div>
                        <span v-if="createSupplyForm.errors.category && selectedCreateCategory !== 'Otros'" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ createSupplyForm.errors.category }}</span>
                    </div>
                    <div v-if="selectedCreateCategory === 'Otros'">
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Especificar categoría</label>
                        <input v-model="customCreateCategory"
                            @input="onCustomCategoryInput"
                            placeholder="Nombre de la categoría"
                            style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px"
                            @focus="$event.target.style.borderColor='#f97316'"
                            @blur="$event.target.style.borderColor='#e2e8f0'" />
                        <span v-if="createSupplyForm.errors.category" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ createSupplyForm.errors.category }}</span>
                    </div>
                    <div class="custom-select-unit" :style="openUnitDropdown ? 'position:relative;z-index:40' : 'position:relative;z-index:10'">
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Unidad</label>
                        <div class="custom-dropdown-container">
                            <button
                                type="button"
                                class="custom-dropdown-btn"
                                :class="{ 'is-active': openUnitDropdown }"
                                @click="toggleUnitDropdown"
                            >
                                <span :class="{ 'placeholder': !createSupplyForm.unit }">
                                    {{ createSupplyForm.unit || 'Selecciona una unidad' }}
                                </span>
                                <svg class="custom-dropdown-arrow" :class="{ 'is-rotated': openUnitDropdown }" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <transition name="dropdown-anim">
                                <ul v-if="openUnitDropdown" class="custom-dropdown-menu">
                                    <li
                                        v-for="u in availableUnits"
                                        :key="u"
                                        class="custom-dropdown-item"
                                        :class="{ 'is-selected': createSupplyForm.unit === u }"
                                        @click="selectUnit(u)"
                                    >
                                        <span>{{ u }}</span>
                                        <svg v-if="createSupplyForm.unit === u" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </li>
                                </ul>
                            </transition>
                        </div>
                        <span v-if="createSupplyForm.errors.unit" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ createSupplyForm.errors.unit }}</span>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Precio ($)</label>
                        <div style="position:relative;margin-top:4px">
                            <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;font-weight:600">$</span>
                            <input 
                                :value="formattedCreateCost"
                                @input="onCostCreateInput"
                                type="text"
                                inputmode="numeric"
                                placeholder="18.000"
                                style="width:100%;padding:8px 10px 8px 24px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box"
                                @focus="$event.target.style.borderColor='#f97316'"
                                @blur="$event.target.style.borderColor='#e2e8f0'"
                            />
                        </div>
                        <span v-if="createSupplyForm.errors.cost" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ createSupplyForm.errors.cost }}</span>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Stock inicial (Entero)</label>
                        <input v-model="createSupplyForm.stock" type="number" step="1" min="0" placeholder="0"
                            style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px" />
                        <span v-if="createSupplyForm.errors.stock" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ createSupplyForm.errors.stock }}</span>
                    </div>
                    <div>
                        <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase">Stock mínimo (Entero)</label>
                        <input v-model="createSupplyForm.minimum_stock" type="number" step="1" min="0" placeholder="5"
                            style="width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;margin-top:4px" />
                        <span v-if="createSupplyForm.errors.minimum_stock" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ createSupplyForm.errors.minimum_stock }}</span>
                    </div>
                </div>
                <div style="display:flex;gap:8px">
                    <button @click="submitCreateSupply" :disabled="createSupplyForm.processing"
                        style="padding:8px 18px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">
                        {{ createSupplyForm.processing ? 'Guardando...' : 'Crear insumo' }}
                    </button>
                    <button @click="cancelCreateSupply"
                        style="padding:8px 18px;background:#f1f5f9;color:#64748b;border:none;border-radius:8px;font-size:13px;cursor:pointer">
                        Cancelar
                    </button>
                </div>
            </div>

            <!-- Tabla -->
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:14px 18px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Nombre</th>
                        <th style="padding:14px 18px;text-align:left;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Categoría</th>
                        <th style="padding:14px 18px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Unidad</th>
                        <th style="padding:14px 18px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Precio</th>
                        <th style="padding:14px 18px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Stock</th>
                        <th style="padding:14px 18px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Mínimo</th>
                        <th style="padding:14px 18px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Estado</th>
                        <th style="padding:14px 18px;text-align:center;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Acciones</th>
                    </tr>
                </thead>
                <tbody v-if="supplies && supplies.length > 0">
                    <tr
                        v-for="supply in supplies"
                        :key="supply.id"
                        :style="`border-top:1px solid #eef2f7;transition:.2s;${editingSupply === supply.id ? 'background:#fffbf5;' : ''}`">

                        <!-- Nombre -->
                        <td style="padding:12px 18px">
                            <template v-if="editingSupply === supply.id">
                                <input v-model="supplyForm.name" placeholder="Nombre"
                                    style="width:100%;padding:6px 8px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;outline:none;box-sizing:border-box" />
                                <span v-if="supplyForm.errors.name" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ supplyForm.errors.name }}</span>
                            </template>
                            <span v-else style="font-size:14px;font-weight:500;color:#1e293b">{{ supply.name }}</span>
                        </td>

                        <!-- Categoría -->
                        <td style="padding:12px 18px">
                            <template v-if="editingSupply === supply.id">
                                <select v-model="selectedEditCategory"
                                    @change="onEditCategorySelectChange"
                                    style="width:100%;padding:6px 8px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;outline:none;box-sizing:border-box;background-color:#ffffff;color:#1e293b;cursor:pointer">
                                    <option value="" disabled>Seleccionar</option>
                                    <option v-for="cat in availableCategories" :key="cat" :value="cat">{{ cat }}</option>
                                </select>
                                <input v-if="selectedEditCategory === 'Otros'"
                                    v-model="customEditCategory"
                                    @input="onCustomEditCategoryInput"
                                    placeholder="Especificar categoría"
                                    style="width:100%;padding:6px 8px;border:1.5px solid #f97316;border-radius:6px;font-size:12px;outline:none;box-sizing:border-box;margin-top:4px" />
                                <span v-if="supplyForm.errors.category" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ supplyForm.errors.category }}</span>
                            </template>
                            <span v-else style="font-size:13px;color:#64748b">{{ supply.category }}</span>
                        </td>

                        <!-- Unidad -->
                        <td style="padding:12px 18px;text-align:center">
                            <template v-if="editingSupply === supply.id">
                                <select v-model="supplyForm.unit"
                                    style="width:85px;padding:6px 4px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:center;outline:none;background-color:#ffffff;color:#1e293b;cursor:pointer">
                                    <option value="" disabled>Unidad</option>
                                    <option v-for="u in availableUnits" :key="u" :value="u">{{ u }}</option>
                                </select>
                                <span v-if="supplyForm.errors.unit" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ supplyForm.errors.unit }}</span>
                            </template>
                            <span v-else style="font-size:13px;color:#475569">{{ supply.unit }}</span>
                        </td>

                        <!-- Costo / Precio -->
                        <td style="padding:12px 18px;text-align:center">
                            <template v-if="editingSupply === supply.id">
                                <div style="position:relative;display:inline-block">
                                    <span style="position:absolute;left:8px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12px;font-weight:600">$</span>
                                    <input 
                                        :value="formattedEditCost"
                                        @input="onCostEditInput"
                                        type="text"
                                        inputmode="numeric"
                                        placeholder="18.000"
                                        style="width:105px;padding:6px 6px 6px 20px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:left;outline:none;box-sizing:border-box"
                                    />
                                </div>
                                <span v-if="supplyForm.errors.cost" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ supplyForm.errors.cost }}</span>
                            </template>
                            <span v-else style="font-size:13px;font-weight:600;color:#0f172a">{{ formatPrice(supply.cost) }}</span>
                        </td>

                        <!-- Stock -->
                        <td style="padding:12px 18px;text-align:center">
                            <template v-if="editingSupply === supply.id">
                                <input v-model="supplyForm.stock" type="number" step="1" min="0" placeholder="0"
                                    style="width:75px;padding:6px 4px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:center;outline:none" />
                                <span v-if="supplyForm.errors.stock" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ supplyForm.errors.stock }}</span>
                            </template>
                            <span v-else :style="`font-size:14px;font-weight:700;${Math.round(Number(supply.stock)) <= Math.round(Number(supply.minimum_stock)) ? 'color:#dc2626' : 'color:#15803d'}`">
                                {{ Math.round(Number(supply.stock)) }}
                            </span>
                        </td>

                        <!-- Mínimo -->
                        <td style="padding:12px 18px;text-align:center">
                            <template v-if="editingSupply === supply.id">
                                <input v-model="supplyForm.minimum_stock" type="number" step="1" min="0" placeholder="0"
                                    style="width:75px;padding:6px 4px;border:1.5px solid #f97316;border-radius:6px;font-size:13px;text-align:center;outline:none" />
                                <span v-if="supplyForm.errors.minimum_stock" style="color:#dc2626;font-size:11px;display:block;margin-top:2px">{{ supplyForm.errors.minimum_stock }}</span>
                            </template>
                            <span v-else style="font-size:13px;color:#334155">{{ Math.round(Number(supply.minimum_stock)) }}</span>
                        </td>

                        <!-- Estado -->
                        <td style="padding:12px 18px;text-align:center">
                            <span
                                v-if="editingSupply === supply.id"
                                :style="`padding:4px 10px;border-radius:9999px;font-size:11px;font-weight:600;${Math.round(Number(supplyForm.stock)) <= Math.round(Number(supplyForm.minimum_stock)) ? 'background:#fee2e2;color:#dc2626' : 'background:#dcfce7;color:#15803d'}`">
                                {{ Math.round(Number(supplyForm.stock)) <= Math.round(Number(supplyForm.minimum_stock)) ? 'Stock Bajo' : 'Disponible' }}
                            </span>
                            <span
                                v-else-if="Math.round(Number(supply.stock)) <= Math.round(Number(supply.minimum_stock))"
                                style="background:#fee2e2;color:#dc2626;padding:4px 10px;border-radius:9999px;font-size:11px;font-weight:600">
                                Stock Bajo
                            </span>
                            <span
                                v-else
                                style="background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:9999px;font-size:11px;font-weight:600">
                                Disponible
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td style="padding:12px 18px;text-align:center;white-space:nowrap">
                            <template v-if="editingSupply === supply.id">
                                <button
                                    @click="saveSupply(supply.id)"
                                    :disabled="supplyForm.processing"
                                    style="padding:5px 12px;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;border-radius:6px;font-size:12px;cursor:pointer;font-weight:500;margin-right:6px">
                                    {{ supplyForm.processing ? '...' : 'Guardar' }}
                                </button>
                                <button
                                    @click="cancelEditSupply"
                                    style="padding:5px 12px;background:#f1f5f9;color:#64748b;border:none;border-radius:6px;font-size:12px;cursor:pointer">
                                    Cancelar
                                </button>
                            </template>
                            <template v-else>
                                <button
                                    @click="startEditSupply(supply)"
                                    style="background:#fff7ed;color:#f97316;border:1px solid #fed7aa;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:500;margin-right:6px">
                                    Editar
                                </button>
                                <button
                                    @click="deleteSupply(supply.id)"
                                    style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:12px;font-weight:500">
                                    Eliminar
                                </button>
                            </template>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr>
                        <td colspan="8" style="padding:2.5rem;text-align:center;color:#94a3b8;font-size:13px">
                            No hay insumos registrados. Haz clic en "+ Nuevo insumo" para agregar el primero.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>
</template>

<style scoped>
.custom-dropdown-container {
    position: relative;
    width: 100%;
}

.custom-dropdown-btn {
    width: 100%;
    padding: 8px 10px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13px;
    background-color: #ffffff;
    color: #1e293b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    outline: none;
    box-sizing: border-box;
    margin-top: 4px;
    text-align: left;
    user-select: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.custom-dropdown-btn:hover {
    border-color: #cbd5e1;
}

.custom-dropdown-btn:focus,
.custom-dropdown-btn.is-active {
    border-color: #f97316;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.15);
}

.custom-dropdown-btn .placeholder {
    color: #94a3b8;
}

.custom-dropdown-arrow {
    width: 16px;
    height: 16px;
    color: #64748b;
    transition: transform 0.2s ease, color 0.2s ease;
    flex-shrink: 0;
    margin-left: 6px;
}

.custom-dropdown-arrow.is-rotated {
    transform: rotate(180deg);
    color: #ea580c;
}

.custom-dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 60;
    background: #ffffff;
    border: 1.5px solid #fed7aa;
    border-radius: 8px;
    box-shadow: 0 10px 25px -5px rgba(234, 88, 12, 0.12), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    max-height: 210px;
    overflow-y: auto;
    padding: 4px;
    margin: 0;
    list-style: none;
}

.custom-dropdown-menu::-webkit-scrollbar {
    width: 6px;
}
.custom-dropdown-menu::-webkit-scrollbar-track {
    background: #fffaf0;
    border-radius: 4px;
}
.custom-dropdown-menu::-webkit-scrollbar-thumb {
    background: #fed7aa;
    border-radius: 4px;
}
.custom-dropdown-menu::-webkit-scrollbar-thumb:hover {
    background: #fb923c;
}

.custom-dropdown-item {
    padding: 8px 10px;
    font-size: 13px;
    border-radius: 6px;
    cursor: pointer;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: background-color 0.15s ease, color 0.15s ease;
    user-select: none;
}

/* Naranja claro en hover */
.custom-dropdown-item:hover {
    background-color: #ffedd5 !important;
    color: #9a3412 !important;
}

/* Elemento seleccionado con tono naranja */
.custom-dropdown-item.is-selected {
    background-color: #fed7aa;
    color: #7c2d12;
    font-weight: 600;
}

.custom-dropdown-item.is-selected:hover {
    background-color: #fdba74 !important;
    color: #7c2d12 !important;
}

.dropdown-anim-enter-active,
.dropdown-anim-leave-active {
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

.dropdown-anim-enter-from,
.dropdown-anim-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.98);
}
</style>