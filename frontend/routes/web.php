<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\WhatsAppAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\PayPalController;
Route::get('/pagos/{order}/paypal', [PayPalController::class, 'checkout'])->name('paypal.checkout');
Route::get('/pagos/{order}/paypal/success', [PayPalController::class, 'success'])->name('paypal.success');
Route::get('/pagos/{order}/paypal/cancel', [PayPalController::class, 'cancel'])->name('paypal.cancel');

// ===== Menu (publico) =====
// Home muestra el catalogo de productos disponibles (ver MenuController::index).
Route::get('/', [MenuController::class, 'index'])->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login']);
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/activate',         [AuthController::class, 'showActivation'])->name('auth.activate');
    Route::post('/activate',        [AuthController::class, 'activate'])->name('auth.activate.verify');
    Route::post('/activate/resend', [AuthController::class, 'resendActivation'])->name('auth.activate.resend');
    Route::get('/2fa/challenge',  [TwoFactorController::class, 'showChallenge'])->name('2fa.challenge');
    Route::post('/2fa/verify',    [TwoFactorController::class, 'verify'])->name('2fa.verify');
    Route::post('/2fa/resend',    [TwoFactorController::class, 'resend'])->name('2fa.resend');
    Route::get('/auth/whatsapp',         [WhatsAppAuthController::class, 'show'])->name('auth.whatsapp');
    Route::post('/auth/whatsapp/send',   [WhatsAppAuthController::class, 'sendOtp'])->name('auth.whatsapp.send');
    Route::get('/auth/whatsapp/verify',  [WhatsAppAuthController::class, 'showVerify'])->name('auth.whatsapp.verify');
    Route::post('/auth/whatsapp/verify', [WhatsAppAuthController::class, 'verifyOtp'])->name('auth.whatsapp.verify.post');
    Route::post('/auth/whatsapp/resend', [WhatsAppAuthController::class, 'resendOtp'])->name('auth.whatsapp.resend');
    Route::get('/forgot-password',  [ForgotPasswordController::class, 'show'])->name('password.forgot');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'send'])->name('password.send');
    Route::get('/reset-password',   [ForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password',  [ForgotPasswordController::class, 'reset'])->name('password.update');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () { return Inertia::render('Dashboard'); })->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable'])->name('2fa.enable');
    Route::get('/perfil',            [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil',            [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/contrasena', [ProfileController::class, 'changePassword'])->name('profile.password');
        Route::patch('/perfil/disponibilidad', [ProfileController::class, 'toggleAvailability'])->name('profile.toggle.availability');
   // Ahora exigen un pedido específico en la URL: /pagos/5, /pagos/12, etc.
Route::get('/pagos/{order}',  [PaymentController::class, 'show'])->name('payments.show');
Route::post('/pagos/{order}', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/pagos/{order}/paypal',         [PayPalController::class, 'checkout'])->name('paypal.checkout');
Route::get('/pagos/{order}/paypal/success', [PayPalController::class, 'success'])->name('paypal.success');
Route::get('/pagos/{order}/paypal/cancel',  [PayPalController::class, 'cancel'])->name('paypal.cancel');
    Route::get('/payments/invoice/{invoice}',  [PaymentController::class, 'invoice'])->name('payments.invoice');
    Route::get('/payments/pending/{payment}',  [PaymentController::class, 'pending'])->name('payments.pending');

    // ===== Entregas (Orders) =====
    // Rutas de cliente: historial, creacion y detalle de pedidos.
    Route::get('/orders',                  [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders',                 [OrderController::class, 'store'])->name('orders.store');
    Route::post('/orders/{order}/ready', [OrderController::class, 'markReady'])->name('orders.markReady');
    Route::get('/orders/{order}',          [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    // Vistas por rol: repartidor ve sus entregas, vendedor ve pedidos libres + propios.
    Route::get('/mis-entregas',            [OrderController::class, 'repartidorOrders'])->name('orders.repartidor');
    Route::get('/mis-pedidos-vendedor',    [OrderController::class, 'vendedorOrders'])->name('orders.vendedor');
    Route::post('/orders/{order}/claim',   [OrderController::class, 'claimOrder'])->name('orders.claim');

    Route::middleware('role:administrador')->group(function () {
        Route::get('/admin/users',                        [UserController::class, 'index'])->name('admin.users');
        Route::post('/admin/users',                       [UserController::class, 'store']);
        Route::patch('/admin/users/{user}/role',          [UserController::class, 'assignRole']);
        Route::patch('/admin/users/{user}/deactivate',    [UserController::class, 'deactivate']);
        Route::get('/admin/payments',                     [PaymentController::class, 'index'])->name('admin.payments');
        Route::patch('/admin/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');

        // ===== Entregas (Orders) - panel admin =====
        Route::get('/admin/orders',                       [OrderController::class, 'adminIndex'])->name('admin.orders');
        Route::patch('/admin/orders/{order}/assign',      [OrderController::class, 'assignRepartidor'])->name('admin.orders.assign');

        Route::get('/admin/inventory',                    [InventoryController::class, 'index'])->name('admin.inventory');
        Route::post('/admin/inventory/{product}', [InventoryController::class, 'update'])->name('admin.inventory.update');
        Route::post('/admin/inventory',                   [InventoryController::class, 'store'])->name('admin.inventory.store');
        Route::delete('/admin/inventory/{product}',       [InventoryController::class, 'destroy'])->name('admin.inventory.destroy');

        // ===== Reportes =====
        // Todas exigen rol administrador (heredado de este grupo de middleware).
        Route::get('/reports',            [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/ventas',     [ReportController::class, 'ventas'])->name('reports.ventas');
        Route::get('/reports/inventario', [ReportController::class, 'inventario'])->name('reports.inventario');
        Route::get('/reports/pedidos',    [ReportController::class, 'pedidos'])->name('reports.pedidos');
        Route::get('/reports/tiempos',    [ReportController::class, 'tiempos'])->name('reports.tiempos');

        Route::get('/admin/supplies', [SupplyController::class,'index'])->name('admin.supplies');
        Route::post('/admin/supplies', [SupplyController::class,'store'])->name('admin.supplies.store');
        Route::post('/admin/supplies/{supply}', [SupplyController::class,'update'])->name('admin.supplies.update');
        Route::delete('/admin/supplies/{supply}', [SupplyController::class,'destroy'])->name('admin.supplies.destroy');
    });
});
