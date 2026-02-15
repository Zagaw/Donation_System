<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Donor;
use App\Models\Receiver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
                'admin' => '/admin',
                'donor' => '/donor',
                'receiver' => '/receiver',
            }
        ]);
    }


    // UPDATE PROFILE
    /*public function update(Request $request)
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
    }*/

        // UPDATE PROFILE
    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->userId, 'userId'),
            ],
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'sometimes|string|min:6|confirmed',
        ];

        $request->validate($rules);

        $updateData = [];

        // Update basic info if provided
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if ($request->has('address')) {
            $updateData['address'] = $request->address;
        }

        // Handle password update
        if ($request->has('new_password')) {
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect'
                ], 422);
            }

            $updateData['password'] = Hash::make($request->new_password);
        }

        // Update user
        $user->update($updateData);

        // Reload user with relationships
        $user->load('donor', 'receiver');

        return response()->json([
            'message' => 'Profile updated successfully',
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

