<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class WhatsAppAuthController extends Controller
{
    public function __construct(private WhatsAppService $whatsapp) {}

    public function show()
    {
        return Inertia::render('Auth/WhatsAppVerify');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => 'required|string|min:10']);

        $phone = preg_replace('/\D/', '', $request->phone);
        if (strlen($phone) === 10) {
            $phone = '57' . $phone;
        }

        $user = User::where('phone', 'like', '%' . substr($phone, -10))->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'No existe una cuenta con este numero. Registrate primero.']);
        }

        $last = VerificationCode::where('user_id', $user->id)
            ->where('type', 'whatsapp_otp')
            ->where('used', false)
            ->latest()
            ->first();

        if ($last && !$last->canResend()) {
            $seconds = $last->secondsUntilResend();
            return back()->withErrors(['phone' => "Espera {$seconds} segundos antes de reenviar."]);
        }

        $verification = VerificationCode::generate($user, 'whatsapp_otp', 'whatsapp', 10);
        $sent = $this->whatsapp->sendOtp($phone, $verification->code);

        if (!$sent) {
            return back()->withErrors(['phone' => 'Error al enviar el mensaje. Intenta de nuevo.']);
        }

        session(['whatsapp_user_id' => $user->id]);

        return redirect()->route('auth.whatsapp.verify')
            ->with('success', 'Codigo enviado a tu WhatsApp.');
    }

    public function showVerify()
    {
        abort_unless(session()->has('whatsapp_user_id'), 403);

        $user = User::findOrFail(session('whatsapp_user_id'));
        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'whatsapp_otp')
            ->where('used', false)
            ->latest()
            ->first();

        return Inertia::render('Auth/WhatsAppVerify', [
            'step'                 => 'otp',
            'seconds_until_resend' => $verification ? $verification->secondsUntilResend() : 0,
            'attempts_left'        => $verification ? ($verification->max_attempts - $verification->attempts) : 5,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);
        abort_unless(session()->has('whatsapp_user_id'), 403);

        $user = User::findOrFail(session('whatsapp_user_id'));

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'whatsapp_otp')
            ->where('used', false)
            ->latest()
            ->first();

        if (!$verification) {
            return back()->withErrors(['code' => 'No hay un codigo activo. Solicita uno nuevo.']);
        }

        $verification->increment('attempts');

        if ($verification->attempts > $verification->max_attempts) {
            session()->forget('whatsapp_user_id');
            return redirect()->route('login')
                ->withErrors(['login' => 'Demasiados intentos. Inicia sesion nuevamente.']);
        }

        if (!$verification->isValid($request->code)) {
            $remaining = $verification->max_attempts - $verification->attempts;
            return back()->withErrors(['code' => "Codigo incorrecto. Te quedan {$remaining} intentos."]);
        }

        $verification->update(['used' => true]);
        $user->update(['is_verified' => true]);
        session()->forget('whatsapp_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        activity()->causedBy($user)->performedOn($user)->log('login_whatsapp_success');

        return $user->hasRole('administrador')
            ? redirect()->route('dashboard')
            : redirect()->route('home');
    }

    public function resendOtp(Request $request)
    {
        abort_unless(session()->has('whatsapp_user_id'), 403);

        $user = User::findOrFail(session('whatsapp_user_id'));

        $last = VerificationCode::where('user_id', $user->id)
            ->where('type', 'whatsapp_otp')
            ->latest()
            ->first();

        if ($last && !$last->canResend()) {
            $seconds = $last->secondsUntilResend();
            return back()->withErrors(['code' => "Espera {$seconds} segundos antes de reenviar."]);
        }

        $verification = VerificationCode::generate($user, 'whatsapp_otp', 'whatsapp', 10);
        $this->whatsapp->sendOtp($user->phone, $verification->code);

        return back()->with('success', 'Codigo reenviado por WhatsApp.');
    }
}