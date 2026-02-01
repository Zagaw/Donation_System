<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DonationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/update-profile', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'allUsers']);
    // Donations
    Route::get('/donations/pending', [AdminController::class, 'pendingDonations']);
    Route::post('/donations/{id}/approve', [AdminController::class, 'approveDonation']);
    Route::post('/donations/{id}/reject', [AdminController::class, 'rejectDonation']);
});

Route::middleware(['auth:sanctum', 'role:receiver'])->prefix('receiver')->group(function () {
    Route::post('/requests', [ReceiverController::class, 'createRequest']);
    Route::get('/requests', [ReceiverController::class, 'myRequests']);
    Route::post('/feedback', [ReceiverController::class, 'submitFeedback']);
});

Route::middleware(['auth:sanctum', 'role:donor'])->prefix('donor')->group(function () {
    Route::post('/donations', [DonationController::class, 'store']);
    Route::get('/donations', [DonationController::class, 'myDonations']);
    Route::delete('/donations/{id}', [DonationController::class, 'destroy']);
});



