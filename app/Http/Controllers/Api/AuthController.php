<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Handle user login and issue plain text token.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah',
            ], 401);
        }

        $user = auth()->user();
        // Load the role relationship
        $user->load('role');

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role ? $user->role->slug : null,
                    'role_name' => $user->role ? $user->role->name : null,
                ],
            ]
        ]);
    }

    /**
     * Revoke current user's token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Retrieve authenticated user details, role, and permissions.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('role.permissions');

        return response()->json([
            'success' => true,
            'message' => 'User profile retrieved successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'role' => $user->role ? $user->role->slug : null,
                'role_name' => $user->role ? $user->role->name : null,
                'permissions' => $user->role ? $user->role->permissions->pluck('slug')->toArray() : [],
            ]
        ]);
    }
}
