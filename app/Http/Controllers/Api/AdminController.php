<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'message' => 'Welcome Admin'
        ]);
    }

    public function allUsers()
    {
        return response()->json(
            User::with('donor', 'receiver')->get()
        );
    }
}

