<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthApiController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        if ($user->status === 'pending') {
            Auth::logout();

            return response()->json(['message' => 'Account awaiting admin approval'], 403);
        }

        if ($user->status !== 'active') {
            Auth::logout();

            return response()->json(['message' => 'Account deactivated'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'country' => ['nullable', 'string'],
            'learning_level' => ['nullable', 'in:beginner,intermediate'],
        ]);

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'role' => 'student',
            'status' => 'pending',
            'learning_level' => $data['learning_level'] ?? 'beginner',
        ]);

        $notifier = app(\App\Services\NotificationService::class);
        foreach (User::whereIn('role', ['super_admin', 'admin'])->where('status', 'active')->get() as $admin) {
            $notifier->notify(
                $admin,
                'student_pending',
                'New student awaiting approval',
                $user->name.' ('.$user->email.') registered and needs confirmation.',
                '/admin/users?status=pending'
            );
        }

        return response()->json([
            'message' => 'Registration received. An admin must approve your account before you can sign in.',
            'user' => $user,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
