<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ForgotPasswordController extends Controller
{
    // Mostrar formulario de recuperacion
    public function show()
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    // Enviar codigo al correo
    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No encontramos una cuenta con ese correo.',
        ]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => $code,
                'created_at' => now(),
            ]
        );

        $user = User::where('email', $request->email)->first();
        $user->notify(new ResetPasswordNotification($code));

        session(['reset_email' => $request->email]);

        return redirect()->route('password.reset');
    }

    // Mostrar formulario de nueva contrasena
    public function showReset()
    {
        abort_unless(session()->has('reset_email'), 403);
        return Inertia::render('Auth/ResetPassword');
    }

    // Restablecer contrasena
    public function reset(Request $request)
    {
        $request->validate([
            'code'     => 'required|digits:6',
            'password' => 'required|confirmed|min:8',
        ]);

        $email = session('reset_email');

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $request->code)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (!$record) {
            return back()->withErrors(['code' => 'Codigo invalido o expirado.']);
        }

        User::where('email', $email)->update([
            'password' => bcrypt($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();
        session()->forget('reset_email');

        return redirect()->route('login')->with('success', 'Contrasena restablecida correctamente.');
    }
}