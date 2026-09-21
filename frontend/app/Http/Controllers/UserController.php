<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Users', [
            'users' => User::with('roles')->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'role'  => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt('Temporal.123'),
        ]);

        $user->assignRole($data['role']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties(['new_role' => $data['role']])
            ->log('user_created');

        return redirect()->back()->with('success', 'Usuario creado.');
    }

    public function deactivate(User $user)
    {
        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->log('user_deactivated');

        return back()->with('success', 'Usuario desactivado.');
    }

    // RF-029: Asignar rol con auditoria (RN-029)
    public function assignRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|exists:roles,name']);

        $oldRole = $user->getRoleNames()->first();
        $user->syncRoles([$request->role]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'old_role' => $oldRole,
                'new_role' => $request->role,
            ])
            ->log('role_changed');

        return back()->with('success', 'Rol actualizado.');
    }
}