<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleSwitchController extends Controller
{
    public function switch(Request $request)
    {
        $validated = $request->validate([
            'role' => 'required|string',
        ]);

        $user = $request->user();

        if (! $user->hasRole($validated['role'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki role tersebut.');
        }

        $user->setActiveRole($validated['role']);

        return redirect()->back()->with('success', 'Role berhasil diganti ke '.$validated['role']);
    }

    public function roles(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'roles' => $user->roleList(),
            'active_role' => $user->activeRole(),
        ]);
    }
}
