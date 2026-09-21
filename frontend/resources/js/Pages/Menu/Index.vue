<script setup>
import { ref, computed } from 'vue'
import { usePage, router, useForm } from '@inertiajs/vue3'

const { products, categories } = defineProps(['products', 'categories'])
const { auth } = usePage().props
console.log('Productos:', products)
console.log('Categorías:', categories)
console.log('Usuario:', auth.user)

const selectedCategory = ref('Todos')
const cart = ref([])
const showCart = ref(false)
const showNotification = ref(false)
const showUserMenu = ref(false)
const showCheckout = ref(false)
const showCustomize = ref(false)
const customizingProduct = ref(null)
const customNote = ref('')
const lastAdded = ref('')

const checkoutForm = useForm({
    items:          [],
    address:        '',
    payment_method: 'efectivo',
    notes:          '',
})

const filteredProducts = computed(() => {
    if (selectedCategory.value === 'Todos') return products
    return products.filter(p => p.category === selectedCategory.value)
})

const cartTotal       = computed(() => cart.value.reduce((sum, i) => sum + i.price * i.qty, 0))
const cartTax         = computed(() => Math.round(cartTotal.value * 0.19))
const cartGrandTotal  = computed(() => cartTotal.value + cartTax.value)
const cartCount       = computed(() => cart.value.reduce((sum, i) => sum + i.qty, 0))
const role            = computed(() => auth.user?.roles?.[0] ?? null)

// RF-002: Abrir modal de personalizacion antes de agregar
const openCustomize = (product) => {
    if (!auth.user) { router.visit('/login'); return }
    customizingProduct.value = product
    customNote.value = ''
    showCustomize.value = true
}

const confirmAddToCart = () => {
    const product = customizingProduct.value
    const note = customNote.value.trim()

    // Cada combinacion producto+nota distinta es una linea separada del carrito
    const existing = cart.value.find(i => i.id === product.id && i.note === note)
    if (existing) {
        existing.qty++
    } else {
        cart.value.push({ ...product, qty: 1, note })
    }

    lastAdded.value = product.name
    showNotification.value = true
    showCustomize.value = false
    setTimeout(() => showNotification.value = false, 3000)
}

const removeFromCart = (cartIndex) => { cart.value.splice(cartIndex, 1) }

const formatPrice = (p) => '$' + Number(p).toLocaleString('es-CO')

const logout = () => { router.post('/logout'); showUserMenu.value = false }

const initials = computed(() => {
    if (!auth.user) return ''
    return auth.user.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

const submitOrder = () => {
    checkoutForm.items = cart.value.map(i => ({ id: i.id, qty: i.qty, note: i.note || '' }))
    checkoutForm.post('/orders', {
        onSuccess: () => {
            cart.value = []
            showCart.value = false
            showCheckout.value = false
        }
    })
}
</script>

<template>
<div style="min-height:100vh;background:#f8fafc;font-family:'Segoe UI',sans-serif" @click="showUserMenu=false">

    <!-- Notificacion -->
    <transition name="fade">
        <div v-if="showNotification" style="position:fixed;top:80px;right:20px;z-index:1000;background:white;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;display:flex;align-items:center;gap:10px;box-shadow:0 4px 20px rgba(0,0,0,0.12)">
            <div style="width:28px;height:28px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <svg width="14" height="14" fill="none" stroke="#15803d" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div style="font-size:13px;font-weight:600;color:#15803d">Agregado al carrito</div>
                <div style="font-size:12px;color:#64748b">{{ lastAdded }}</div>
            </div>
        </div>
    </transition>

    <!-- Header -->
    <header style="background:linear-gradient(135deg,#f97316,#ea580c);padding:0 2rem;height:70px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 20px rgba(249,115,22,0.3)">
        <div style="cursor:pointer" @click="router.visit('/')">
            <div style="font-size:22px;font-weight:800;color:white;line-height:1">Mi Chuzito</div>
            <div style="font-size:11px;color:rgba(255,255,255,0.8)">Las mejores comidas de la ciudad</div>
        </div>

        <div style="display:flex;align-items:center;gap:10px">
            <button @click.stop="showCart=true" style="position:relative;padding:8px 16px;background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.35);border-radius:8px;color:white;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:8px">
                <svg width="17" height="17" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Tu Pedido
                <span v-if="cartCount > 0" style="position:absolute;top:-8px;right:-8px;background:white;color:#f97316;border-radius:50%;width:20px;height:20px;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center">{{ cartCount }}</span>
            </button>

            <template v-if="!auth.user">
                <button @click="router.visit('/login')" style="padding:8px 18px;background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.35);border-radius:8px;color:white;font-size:13px;font-weight:500;cursor:pointer">Iniciar sesion</button>
                <button @click="router.visit('/login')" style="padding:8px 18px;background:white;border:none;border-radius:8px;color:#f97316;font-size:13px;font-weight:600;cursor:pointer">Registrate</button>
            </template>

            <div v-else style="position:relative" @click.stop>
                <button @click="showUserMenu=!showUserMenu" style="display:flex;align-items:center;gap:10px;background:rgba(255,255,255,0.15);border:1.5px solid rgba(255,255,255,0.35);border-radius:10px;padding:6px 12px;cursor:pointer">
                    <div style="width:32px;height:32px;border-radius:50%;background:white;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#f97316;flex-shrink:0">{{ initials }}</div>
                    <div style="text-align:left">
                        <div style="font-size:13px;font-weight:600;color:white;line-height:1">{{ auth.user.name }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,0.75)">{{ role }}</div>
                    </div>
                    <svg width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24" :style="`transition:transform 0.2s;${showUserMenu ? 'transform:rotate(180deg)' : ''}`"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <transition name="dropdown">
                    <div v-if="showUserMenu" style="position:absolute;right:0;top:calc(100% + 8px);background:white;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 8px 30px rgba(0,0,0,0.12);min-width:220px;overflow:hidden;z-index:200">
                        <div style="padding:14px 16px;background:#fff7ed;border-bottom:1px solid #e2e8f0">
                            <div style="font-size:14px;font-weight:600;color:#1e293b">{{ auth.user.name }}</div>
                            <div style="font-size:12px;color:#64748b;margin-top:2px">{{ auth.user.email }}</div>
                            <span style="display:inline-block;margin-top:6px;font-size:11px;padding:2px 8px;border-radius:20px;background:#f97316;color:white;font-weight:500">{{ role }}</span>
                        </div>
                        <div style="padding:6px 0">
                            <button @click="router.visit('/perfil');showUserMenu=false" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#1e293b;cursor:pointer;display:flex;align-items:center;gap:10px" @mouseover="$event.currentTarget.style.background='#f8fafc'" @mouseout="$event.currentTarget.style.background='none'">
                                <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Mi perfil
                            </button>
                            <button v-if="role === 'cliente'" @click="router.visit('/orders');showUserMenu=false" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#1e293b;cursor:pointer;display:flex;align-items:center;gap:10px" @mouseover="$event.currentTarget.style.background='#f8fafc'" @mouseout="$event.currentTarget.style.background='none'">
                                <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                                Mis pedidos
                            </button>
                            <button v-if="role === 'cliente'" @click="router.visit('/pagos');showUserMenu=false" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#1e293b;cursor:pointer;display:flex;align-items:center;gap:10px" @mouseover="$event.currentTarget.style.background='#f8fafc'" @mouseout="$event.currentTarget.style.background='none'">
                                <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                                Mis pagos
                            </button>
                            <button v-if="role === 'repartidor'" @click="router.visit('/mis-entregas');showUserMenu=false" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#1e293b;cursor:pointer;display:flex;align-items:center;gap:10px" @mouseover="$event.currentTarget.style.background='#f8fafc'" @mouseout="$event.currentTarget.style.background='none'">
                                <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                Mis entregas
                            </button>
                            <button v-if="role === 'vendedor'" @click="router.visit('/mis-pedidos-vendedor');showUserMenu=false" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#1e293b;cursor:pointer;display:flex;align-items:center;gap:10px" @mouseover="$event.currentTarget.style.background='#f8fafc'" @mouseout="$event.currentTarget.style.background='none'">
                                <svg width="16" height="16" fill="none" stroke="#64748b" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                                Mis pedidos
                            </button>
                            <template v-if="role === 'administrador'">
                                <div style="height:1px;background:#f1f5f9;margin:4px 0"></div>
                                <button @click="router.visit('/admin/users');showUserMenu=false" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#7c3aed;cursor:pointer;display:flex;align-items:center;gap:10px;font-weight:500" @mouseover="$event.currentTarget.style.background='#faf5ff'" @mouseout="$event.currentTarget.style.background='none'">
                                    <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                    Panel de administracion
                                </button>
                                <button @click="router.visit('/admin/orders');showUserMenu=false" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#7c3aed;cursor:pointer;display:flex;align-items:center;gap:10px;font-weight:500" @mouseover="$event.currentTarget.style.background='#faf5ff'" @mouseout="$event.currentTarget.style.background='none'">
                                    <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                                    Gestion de pedidos
                                </button>
                            </template>
                        </div>
                        <div style="height:1px;background:#f1f5f9"></div>
                        <button @click="logout" style="width:100%;padding:10px 16px;background:none;border:none;text-align:left;font-size:14px;color:#ef4444;cursor:pointer;display:flex;align-items:center;gap:10px" @mouseover="$event.currentTarget.style.background='#fef2f2'" @mouseout="$event.currentTarget.style.background='none'">
                            <svg width="16" height="16" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Cerrar sesion
                        </button>
                    </div>
                </transition>
            </div>
        </div>
    </header>

    <!-- Contenido -->
    <div style="max-width:1200px;margin:0 auto;padding:2rem 1rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
            <h1 style="font-size:28px;font-weight:700;color:#1e293b;margin:0">Prueba Nuestro Menu</h1>
            <div style="font-size:13px;color:#64748b">{{ filteredProducts.length }} productos disponibles</div>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:2rem">
            <button @click="selectedCategory='Todos'" :style="`padding:8px 20px;border-radius:50px;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.2s;${selectedCategory==='Todos' ? 'background:#f97316;color:white;border:none' : 'background:white;color:#64748b;border:1.5px solid #e2e8f0'}`">Todos</button>
            <button v-for="cat in categories" :key="cat" @click="selectedCategory=cat" :style="`padding:8px 20px;border-radius:50px;font-size:14px;font-weight:500;cursor:pointer;transition:all 0.2s;${selectedCategory===cat ? 'background:#f97316;color:white;border:none' : 'background:white;color:#64748b;border:1.5px solid #e2e8f0'}`">{{ cat }}</button>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:1.5rem">
            <div v-for="product in filteredProducts" :key="product.id" style="background:white;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;transition:all 0.25s" @mouseover="$event.currentTarget.style.transform='translateY(-4px)';$event.currentTarget.style.boxShadow='0 12px 40px rgba(0,0,0,0.1)'" @mouseout="$event.currentTarget.style.transform='none';$event.currentTarget.style.boxShadow='none'">
                <div style="height:210px;overflow:hidden;position:relative;background:#f1f5f9">
                    <img :src="product.image_url_full" :alt="product.name" style="width:100%;height:100%;object-fit:cover;transition:transform 0.3s" loading="lazy"/>
                    <div style="position:absolute;top:10px;right:10px;background:white;border-radius:20px;padding:3px 10px;font-size:11px;font-weight:600;color:#f97316;box-shadow:0 2px 8px rgba(0,0,0,0.1)">{{ product.category }}</div>
                    <div v-if="!product.is_available" style="position:absolute;inset:0;background:rgba(0,0,0,0.55);display:flex;align-items:center;justify-content:center">
                        <span style="color:white;font-weight:700;font-size:15px;background:rgba(0,0,0,0.4);padding:6px 16px;border-radius:8px">Agotado</span>
                    </div>
                </div>
                <div style="padding:1.1rem">
                    <div style="font-size:16px;font-weight:700;color:#1e293b;margin-bottom:5px">{{ product.name }}</div>
                    <div style="font-size:13px;color:#64748b;margin-bottom:14px;line-height:1.5;min-height:38px">{{ product.description }}</div>
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="font-size:20px;font-weight:800;color:#f97316">{{ formatPrice(product.price) }}</span>
                        <button v-if="product.is_available" @click="openCustomize(product)" style="padding:9px 20px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer">Agregar</button>
                        <span v-else style="font-size:13px;color:#94a3b8;font-style:italic">No disponible</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RF-002: Modal de personalizacion -->
    <transition name="fade">
        <div v-if="showCustomize" style="position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5)" @click.self="showCustomize=false">
            <div style="background:white;border-radius:16px;padding:1.75rem;max-width:420px;width:90%">
                <div style="display:flex;gap:12px;margin-bottom:1.25rem">
                    <img :src="customizingProduct?.image_url_full" :alt="customizingProduct?.name" style="width:60px;height:60px;border-radius:10px;object-fit:cover;flex-shrink:0"/>
                    <div>
                        <div style="font-size:16px;font-weight:700;color:#1e293b">{{ customizingProduct?.name }}</div>
                        <div style="font-size:15px;font-weight:700;color:#f97316;margin-top:2px">{{ formatPrice(customizingProduct?.price) }}</div>
                    </div>
                </div>

                <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Personalizacion (opcional)</label>
                <textarea v-model="customNote" placeholder="Ej: sin cebolla, salsa aparte, extra picante..."
                    style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px;resize:vertical;min-height:80px"
                    @focus="$event.target.style.borderColor='#f97316'"
                    @blur="$event.target.style.borderColor='#e2e8f0'"></textarea>
                <p style="font-size:11px;color:#94a3b8;margin-top:6px">Indica salsas, acompaÃ±amientos o cualquier preferencia para este producto.</p>

                <div style="display:flex;gap:8px;margin-top:1.25rem">
                    <button @click="showCustomize=false" style="flex:1;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;background:white;font-size:13px;color:#64748b;cursor:pointer">Cancelar</button>
                    <button @click="confirmAddToCart" style="flex:2;padding:11px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer">Agregar al carrito</button>
                </div>
            </div>
        </div>
    </transition>

    <!-- Carrito lateral -->
    <transition name="fade">
        <div v-if="showCart" style="position:fixed;inset:0;z-index:200;display:flex;justify-content:flex-end" @click.self="showCart=false">
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.35)" @click="showCart=false"></div>
            <div style="position:relative;width:400px;background:white;height:100vh;overflow-y:auto;display:flex;flex-direction:column;box-shadow:-8px 0 40px rgba(0,0,0,0.15)">

                <div style="background:linear-gradient(135deg,#f97316,#ea580c);padding:1.25rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-shrink:0">
                    <div>
                        <span style="font-size:18px;font-weight:700;color:white">Tu Pedido</span>
                        <span v-if="cartCount > 0" style="font-size:13px;color:rgba(255,255,255,0.8);margin-left:8px">{{ cartCount }} items</span>
                    </div>
                    <button @click="showCart=false" style="background:rgba(255,255,255,0.2);border:none;color:white;cursor:pointer;width:30px;height:30px;border-radius:50%;font-size:16px;display:flex;align-items:center;justify-content:center">x</button>
                </div>

                <div style="flex:1;padding:1rem;overflow-y:auto">
                    <div v-if="cart.length === 0" style="text-align:center;padding:4rem 1rem;color:#94a3b8">
                        <svg width="64" height="64" fill="none" stroke="#e2e8f0" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1.5rem;display:block"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                        <div style="font-size:16px;font-weight:600;color:#64748b;margin-bottom:6px">Tu carrito esta vacio</div>
                        <div style="font-size:13px">Agrega algunos chuzos deliciosos!</div>
                    </div>

                    <div v-for="(item, idx) in cart" :key="idx" style="display:flex;gap:12px;padding:14px 0;border-bottom:1px solid #f1f5f9">
                        <img :src="item.image_url_full" :alt="item.name" style="width:64px;height:64px;border-radius:10px;object-fit:cover;flex-shrink:0"/>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:14px;font-weight:600;color:#1e293b;margin-bottom:2px">{{ item.name }}</div>
                            <div style="font-size:14px;color:#f97316;font-weight:700">{{ formatPrice(item.price) }}</div>
                            <div v-if="item.note" style="font-size:12px;color:#64748b;background:#fff7ed;border-radius:6px;padding:4px 8px;margin-top:4px">Nota: {{ item.note }}</div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px">
                                <div style="display:flex;align-items:center;gap:6px;background:#f8fafc;border-radius:8px;padding:4px 8px">
                                    <button @click="item.qty > 1 ? item.qty-- : removeFromCart(idx)" style="width:22px;height:22px;border-radius:6px;border:none;background:white;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:#64748b;box-shadow:0 1px 3px rgba(0,0,0,0.1)">-</button>
                                    <span style="font-size:14px;font-weight:600;min-width:16px;text-align:center">{{ item.qty }}</span>
                                    <button @click="item.qty++" style="width:22px;height:22px;border-radius:6px;border:none;background:#f97316;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;color:white;box-shadow:0 1px 3px rgba(249,115,22,0.3)">+</button>
                                </div>
                                <div style="font-size:14px;font-weight:600;color:#1e293b">{{ formatPrice(item.price * item.qty) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="cart.length > 0" style="padding:1.5rem;border-top:1px solid #e2e8f0;flex-shrink:0;background:white">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:4px">
                        <span>Subtotal</span><span>{{ formatPrice(cartTotal) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:10px">
                        <span>IVA (19%)</span><span>{{ formatPrice(cartTax) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                        <span style="font-size:16px;font-weight:600;color:#1e293b">Total</span>
                        <span style="font-size:22px;font-weight:800;color:#f97316">{{ formatPrice(cartGrandTotal) }}</span>
                    </div>
                    <button @click="showCheckout=true;showCart=false" style="width:100%;padding:14px;background:linear-gradient(135deg,#f97316,#ea580c);color:white;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;box-shadow:0 4px 15px rgba(249,115,22,0.35)">
                        Proceder al pago
                    </button>
                    <button @click="cart=[]" style="width:100%;padding:10px;background:none;border:none;color:#94a3b8;font-size:13px;cursor:pointer;margin-top:8px">Vaciar carrito</button>
                </div>
            </div>
        </div>
    </transition>

    <!-- Checkout modal -->
    <transition name="fade">
        <div v-if="showCheckout" style="position:fixed;inset:0;z-index:300;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5)">
            <div style="background:white;border-radius:16px;padding:2rem;max-width:480px;width:90%;max-height:90vh;overflow-y:auto">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
                    <h2 style="font-size:18px;font-weight:700;color:#1e293b;margin:0">Confirmar pedido</h2>
                    <button @click="showCheckout=false;showCart=true" style="background:none;border:none;cursor:pointer;color:#64748b;font-size:20px">x</button>
                </div>

                <div v-if="checkoutForm.errors.items" style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;padding:10px;border-radius:8px;font-size:13px;margin-bottom:1rem">
                    {{ checkoutForm.errors.items }}
                </div>

                <div style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Direccion de entrega</label>
                    <input v-model="checkoutForm.address" placeholder="Ingresa tu direccion completa"
                        style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"/>
                    <span v-if="checkoutForm.errors.address" style="color:#dc2626;font-size:11px">{{ checkoutForm.errors.address }}</span>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Metodo de pago</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px">
                        <div @click="checkoutForm.payment_method='efectivo'" :style="`padding:12px;border-radius:8px;border:2px solid;text-align:center;cursor:pointer;${checkoutForm.payment_method==='efectivo' ? 'border-color:#f97316;background:#fff7ed' : 'border-color:#e2e8f0'}`">
                            <div style="font-size:13px;font-weight:500" :style="checkoutForm.payment_method==='efectivo' ? 'color:#f97316' : 'color:#64748b'">Efectivo</div>
                        </div>
                        <div @click="checkoutForm.payment_method='paypal'" :style="`padding:12px;border-radius:8px;border:2px solid;text-align:center;cursor:pointer;${checkoutForm.payment_method==='paypal' ? 'border-color:#f97316;background:#fff7ed' : 'border-color:#e2e8f0'}`">
                            <div style="font-size:13px;font-weight:500" :style="checkoutForm.payment_method==='paypal' ? 'color:#f97316' : 'color:#64748b'">PayPal</div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:1.5rem">
                    <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em">Notas generales del pedido (opcional)</label>
                    <textarea v-model="checkoutForm.notes" placeholder="Instrucciones especiales de entrega..."
                        style="width:100%;padding:11px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;margin-top:6px;resize:vertical;min-height:70px"
                        @focus="$event.target.style.borderColor='#f97316'"
                        @blur="$event.target.style.borderColor='#e2e8f0'"></textarea>
                </div>

                <!-- Resumen -->
                <div style="background:#f8fafc;border-radius:10px;padding:1rem;margin-bottom:1.5rem">
                    <div v-for="(item, idx) in cart" :key="idx" style="margin-bottom:6px">
                        <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b">
                            <span>{{ item.name }} x{{ item.qty }}</span>
                            <span>{{ formatPrice(item.price * item.qty) }}</span>
                        </div>
                        <div v-if="item.note" style="font-size:11px;color:#94a3b8;margin-top:2px">Nota: {{ item.note }}</div>
                    </div>
                    <div style="height:1px;background:#e2e8f0;margin:8px 0"></div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:4px">
                        <span>Subtotal</span><span>{{ formatPrice(cartTotal) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;margin-bottom:4px">
                        <span>IVA (19%)</span><span>{{ formatPrice(cartTax) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;color:#1e293b;margin-top:8px">
                        <span>Total</span><span style="color:#f97316">{{ formatPrice(cartGrandTotal) }}</span>
                    </div>
                </div>

                <button @click="submitOrder" :disabled="checkoutForm.processing || !checkoutForm.address"
                    :style="`width:100%;padding:14px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;${checkoutForm.processing || !checkoutForm.address ? 'background:#e2e8f0;color:#94a3b8;cursor:not-allowed' : 'background:linear-gradient(135deg,#f97316,#ea580c);color:white'}`">
                    {{ checkoutForm.processing ? 'Procesando...' : 'Confirmar pedido' }}
                </button>
            </div>
        </div>
    </transition>

</div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.dropdown-enter-active, .dropdown-leave-active { transition: all 0.2s ease; }
.dropdown-enter-from, .dropdown-leave-to { opacity: 0; transform: translateY(-8px); }
</style>