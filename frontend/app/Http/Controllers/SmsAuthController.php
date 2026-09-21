<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Kreait\Firebase\Factory;

class SmsAuthController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/SmsVerify');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone'          => 'required|string',
            'firebase_token' => 'required|string',
        ]);

        try {
            // Verificar el token con Firebase Admin SDK
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials'));
            $auth    = $factory->createAuth();
            $token   = $auth->verifyIdToken($request->firebase_token);
            $uid     = $token->claims()->get('sub');

            // Normalizar telefono
            $phone = preg_replace('/\s+/', '', $request->phone);
            if (!str_starts_with($phone, '+')) {
                $phone = '+57' . $phone;
            }

            // Buscar o crear usuario
            $user = User::where('phone', $phone)->first();

            if (!$user) {
                return back()->withErrors(['phone' => 'No existe una cuenta con este numero. Registrate primero.']);
            }

            if (!$user->is_active) {
                return back()->withErrors(['phone' => 'Tu cuenta esta desactivada.']);
            }

            $user->update(['is_verified' => true]);

            Auth::login($user);
            $request->session()->regenerate();

            if ($user->hasRole('administrador')) {
                return redirect()->route('dashboard');
            }

            return redirect()->route('home');

        } catch (\Exception $e) {
            return back()->withErrors(['phone' => 'Verificacion fallida. Intenta de nuevo.']);
        }
    }
}