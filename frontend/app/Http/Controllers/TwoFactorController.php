<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TwoFactorController extends Controller
{
    public function showChallenge()
    {
        abort_unless(session()->has('2fa_user_id'), 403);
        $user = User::findOrFail(session('2fa_user_id'));

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_2fa')
            ->where('used', false)
            ->latest()
            ->first();

        return Inertia::render('Auth/TwoFactor', [
            'seconds_until_resend' => $verification ? $verification->secondsUntilResend() : 0,
            'attempts_left'        => $verification ? ($verification->max_attempts - $verification->attempts) : 5,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = User::findOrFail(session('2fa_user_id'));

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_2fa')
            ->where('used', false)
            ->latest()
            ->first();

        if (!$verification) {
            return back()->withErrors(['code' => 'No hay un codigo activo. Solicita uno nuevo.']);
        }

        $verification->increment('attempts');

        if ($verification->attempts > $verification->max_attempts) {
            session()->forget('2fa_user_id');

            activity()->causedBy($user)->performedOn($user)
                ->withProperties(['reason' => '2fa_max_attempts'])
                ->log('suspicious_activity_alert');

            return redirect()->route('login')
                ->withErrors(['login' => 'Demasiados intentos fallidos. Inicia sesion nuevamente.']);
        }

        if (!$verification->isValid($request->code)) {
            $remaining = $verification->max_attempts - $verification->attempts;

            activity()->causedBy($user)->performedOn($user)->log('2fa_failed');

            return back()->withErrors(['code' => "Codigo invalido o expirado. Te quedan {$remaining} intentos."]);
        }

        $verification->update(['used' => true]);
        session()->forget('2fa_user_id');
        Auth::login($user);
        $request->session()->regenerate();

        activity()->causedBy($user)->performedOn($user)->log('login_success');

        if ($user->hasRole('administrador')) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('home');
    }

    public function resend(Request $request)
    {
        abort_unless(session()->has('2fa_user_id'), 403);

        $user = User::findOrFail(session('2fa_user_id'));

        $last = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_2fa')
            ->latest()
            ->first();

        if ($last && !$last->canResend()) {
            $seconds = $last->secondsUntilResend();
            return back()->withErrors(['code' => "Espera {$seconds} segundos antes de reenviar."]);
        }

        $verification = VerificationCode::generate($user, 'email_2fa', 'email', 10);

        if ($user->email) {
            $user->notify(new TwoFactorCodeNotification($verification->code));
        }

        return back()->with('success', 'Codigo reenviado correctamente.');
    }

    public function enable(Request $request)
    {
        $request->user()->update(['two_factor_enabled' => true]);
        return back()->with('success', '2FA activado correctamente.');
    }
}