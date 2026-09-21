<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileApiController extends Controller
{
    // GET /api/profile
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user()->load('profile', 'roles'),
        ]);
    }

    // PUT /api/profile
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'    => 'sometimes|string|max:100',
            'phone'   => 'nullable|string|unique:users,phone,' . $user->id,
            'address' => 'nullable|string|max:255',
            'city'    => 'nullable|string|max:100',
        ]);

        $user->update($request->only(['name', 'phone']));

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['address', 'city', 'department'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente.',
            'data'    => $user->fresh()->load('profile'),
        ]);
    }
}