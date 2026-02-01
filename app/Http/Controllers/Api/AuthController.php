<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donor;
use App\Models\Receiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,donor,receiver',
            'donorType' => 'required_if:role,donor|in:personal,organization',
            'receiverType' => 'required_if:role,receiver|in:personal,organization'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => $request->role
        ]);

        if ($user->role === 'donor') {
            Donor::create([
                'userId' => $user->userId,
                'donorType' => $request->donorType
            ]);
        }

        if ($user->role === 'receiver') {
            Receiver::create([
                'userId' => $user->userId,
                'receiverType' => $request->receiverType
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('donor', 'receiver')
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('donor', 'receiver');

        return response()->json([
            'user' => $user,
            'redirect' => match ($user->role) {
                'admin' => '/admin/dashboard',
                'donor' => '/donor/donations',
                'receiver' => '/receiver/requests',
            }
        ]);
    }


    // UPDATE PROFILE
    public function update(Request $request)
    {
        $user = $request->user();

        $user->update($request->only([
            'name',
            'phone',
            'address'
        ]));

        return response()->json([
            'message' => 'Profile updated',
            'user' => $user
        ]);
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}

