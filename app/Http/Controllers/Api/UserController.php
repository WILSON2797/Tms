<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('id', 'desc')->get();
        return response()->json([
            'success' => true,
            'message' => 'Daftar user berhasil diambil',
            'data' => UserResource::collection($users)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil disimpan',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show($id)
    {
        $user = User::with('role')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail user berhasil diambil',
            'data' => new UserResource($user)
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->load('role');

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diperbarui',
            'data' => new UserResource($user)
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus'
        ]);
    }
}
