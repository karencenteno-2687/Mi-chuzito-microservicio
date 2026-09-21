<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\TwoFactorCodeNotification;
use App\Notifications\AccountActivationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Login');
    }

    public function register(Request $request)
    {
        // Limpiar espacios al inicio/final antes de validar
        $request->merge([
            'name' => trim($request->input('name', '')),
        ]);

        $request->validate([
            // Permite letras y espacios entre palabras, pero no solo espacios
            'name'     => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ]+(\s[a-zA-ZáéíóúÁÉÍÓÚñÑ]+)*$/'],
            'email'    => 'required_without:phone|email|unique:users,email|nullable',
            'phone'    => 'required_without:email|string|unique:users,phone|nullable',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.regex'    => 'El nombre solo puede contener letras y espacios entre palabras, sin caracteres especiales ni numeros.',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'password'    => bcrypt($request->password),
            'is_verified' => false,
        ]);

        $user->assignRole('cliente');

        $verification = VerificationCode::generate($user, 'account_activation', 'email', 10);

        if ($user->email) {
            $user->notify(new AccountActivationNotification($verification->code));
        }

        activity()->causedBy($user)->performedOn($user)->log('user_registered');

        session(['activation_user_id' => $user->id]);

        return redirect()->route('auth.activate');
    }

    public function showActivation()
    {
        abort_unless(session()->has('activation_user_id'), 403);
        return Inertia::render('Auth/Activate');
    }

    public function activate(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $user = User::findOrFail(session('activation_user_id'));

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'account_activation')
            ->where('used', false)
            ->latest()
            ->first();

        if (!$verification) {
            return back()->withErrors(['code' => 'No hay un codigo activo. Solicita uno nuevo.']);
        }

        $verification->increment('attempts');

        if ($verification->attempts > $verification->max_attempts) {
            activity()->causedBy($user)->performedOn($user)->log('activation_too_many_attempts');
            return back()->withErrors(['code' => 'Demasiados intentos fallidos. Solicita un nuevo codigo.']);
        }

        if (!$verification->isValid($request->code)) {
            $remaining = $verification->max_attempts - $verification->attempts;
            return back()->withErrors(['code' => "Codigo invalido o expirado. Te quedan {$remaining} intentos."]);
        }

        $verification->update(['used' => true]);
        $user->update(['is_verified' => true]);

        activity()->causedBy($user)->performedOn($user)->log('account_activated');

        session()->forget('activation_user_id');
        Auth::login($user);

        return redirect()->route('home')->with('success', 'Cuenta activada correctamente.');
    }

    public function resendActivation(Request $request)
    {
        abort_unless(session()->has('activation_user_id'), 403);

        $user = User::findOrFail(session('activation_user_id'));

        $last = VerificationCode::where('user_id', $user->id)
            ->where('type', 'account_activation')
            ->latest()
            ->first();

        if ($last && !$last->canResend()) {
            $seconds = $last->secondsUntilResend();
            return back()->withErrors(['code' => "Espera {$seconds} segundos antes de reenviar."]);
        }

        $verification = VerificationCode::generate($user, 'account_activation', 'email', 10);

        if ($user->email) {
            $user->notify(new AccountActivationNotification($verification->code));
        }

        return back()->with('success', 'Codigo reenviado correctamente.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [$field => $request->login, 'password' => $request->password];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $attemptedUser = User::where($field, $request->login)->first();

            activity()
                ->causedBy($attemptedUser)
                ->withProperties([
                    'login_attempted' => $request->login,
                    'ip'              => $request->ip(),
                    'user_agent'      => $request->userAgent(),
                ])
                ->log('login_failed');

            if ($attemptedUser) {
                $recentFailures = \Spatie\Activitylog\Models\Activity::where('subject_id', $attemptedUser->id)
                    ->where('subject_type', User::class)
                    ->where('description', 'login_failed')
                    ->where('created_at', '>=', now()->subMinutes(15))
                    ->count();

                if ($recentFailures >= 5) {
                    activity()
                        ->causedBy($attemptedUser)
                        ->performedOn($attemptedUser)
                        ->withProperties(['failed_attempts' => $recentFailures])
                        ->log('suspicious_activity_alert');
                }
            }

            return back()->withErrors(['login' => 'Credenciales incorrectas.']);
        }

        $user = Auth::user();

        if (!$user->is_verified) {
            Auth::logout();
            $verification = VerificationCode::generate($user, 'account_activation', 'email', 10);
            if ($user->email) {
                $user->notify(new AccountActivationNotification($verification->code));
            }
            session(['activation_user_id' => $user->id]);
            return redirect()->route('auth.activate')
                ->with('info', 'Tu cuenta no esta activada. Te enviamos un nuevo codigo.');
        }

        Auth::logout();
        $verification = VerificationCode::generate($user, 'email_2fa', 'email', 10);
        if ($user->email) {
            $user->notify(new TwoFactorCodeNotification($verification->code));
        }
        session(['2fa_user_id' => $user->id]);

        activity()->causedBy($user)->performedOn($user)->log('login_password_success');

        return redirect()->route('2fa.challenge');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $request->user()->tokens()->delete();

        activity()->causedBy($user)->performedOn($user)->log('logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}