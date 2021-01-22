<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Models\UserAdmin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'email' => $request->username,
            'password' => $request->password,
        ];

        if (! Auth::once($credentials)) {
            \abort(401, 'Username and password is incorrect.');
        }

        $user = Auth::user();
        $user->tokens()->delete();

        return response()->json([
            'token' => $user->createToken('admin-token', ['user:admin'])->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function update(Request $request, UserAdmin $user)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'username' => ['required', 'string', Rule::unique('user_admins', 'email')->ignore($user->id)],
            'password' => 'required|string|confirmed',
            'oldPassword' => 'required|string',
        ]);

        $matched = Hash::check($data['oldPassword'], $user->password);
        if (! $matched) {
            return response()->json([
                'message' => 'Update failed. Your old password does not match.',
            ], 403);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['username'],
            'password' => Hash::make($data['password']),
        ]);

        return \response()->json([
            'message' => 'Account updated.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
