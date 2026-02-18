<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\InterestController;
use App\Http\Controllers\Api\AdminMatchController;
use App\Http\Controllers\Api\AdminExecutionController;
use App\Http\Controllers\Api\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/update-profile', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);

    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);

    Route::get('/matches/{id}', [AdminMatchController::class, 'getMatchForUser']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'allUsers']);
    Route::get('/status-counts', [AdminController::class, 'getStatusCounts']);
    // Donations
    Route::get('/donations', [AdminController::class, 'allDonations']);
    Route::get('/donations/pending', [AdminController::class, 'pendingDonations']);
    Route::get('/donations/approved', [AdminController::class, 'approvedDonations']);
    Route::get('/donations/rejected', [AdminController::class, 'rejectedDonations']);
    Route::get('/donations/matched', [AdminController::class, 'matchedDonations']);
    Route::get('/donations/executed', [AdminController::class, 'executedDonations']);
    Route::get('/donations/completed', [AdminController::class, 'completedDonations']);
    Route::get('/donations/{id}', [AdminController::class, 'showDonation']);
    Route::post('/donations/{id}/approve', [AdminController::class, 'approveDonation']);
    Route::post('/donations/{id}/reject', [AdminController::class, 'rejectDonation']);

    // Requests
    Route::get('/requests', [AdminController::class, 'allRequests']);
    Route::get('/requests/pending', [AdminController::class, 'pendingRequests']);
    Route::get('/requests/approved', [AdminController::class, 'approvedRequests']);
    Route::get('/requests/rejected', [AdminController::class, 'rejectedRequests']);
    Route::get('/requests/matched', [AdminController::class, 'matchedRequests']);
    Route::get('/requests/executed', [AdminController::class, 'executedRequests']);
    Route::get('/requests/completed', [AdminController::class, 'completedRequests']);
    Route::get('/requests/{id}', [AdminController::class, 'showRequest']);
    Route::post('/requests/{id}/approve', [AdminController::class, 'approveRequest']);
    Route::post('/requests/{id}/reject', [AdminController::class, 'rejectRequest']);

     // interests
    Route::get('/interests', [AdminController::class, 'allInterests']);
    Route::get('/interests/pending', [AdminController::class, 'pendingInterests']);
    Route::get('/interests/approved', [AdminController::class, 'approvedInterests']);
    Route::get('/interests/rejected', [AdminController::class, 'rejectedInterests']);
    Route::get('/interests/completed', [AdminController::class, 'completedInterests']);
    Route::post('/interests/{id}/approve', [AdminController::class, 'approveInterest']);
    Route::post('/interests/{id}/reject', [AdminController::class, 'rejectInterest']);

     // matching
    Route::get('/matches', [AdminMatchController::class, 'getAllMatches']);
    Route::get('/matches/approved', [AdminMatchController::class, 'getApprovedMatches']);
    Route::get('/matches/executed', [AdminMatchController::class, 'getExecutedMatches']);
    Route::get('/matches/completed', [AdminMatchController::class, 'getCompletedMatches']);
    Route::get('/matches/{id}', [AdminMatchController::class, 'getMatchDetails']);
    
    // Matching data
    Route::get('/matching/approved-donations', [AdminMatchController::class, 'getApprovedDonations']);
    Route::get('/matching/approved-requests', [AdminMatchController::class, 'getApprovedRequests']);
    Route::get('/matching/approved-interests', [AdminMatchController::class, 'getApprovedInterests']);
    Route::get('/matching/matched-interests', [AdminMatchController::class, 'getMatchedInterests']); // New route
    
    Route::post('/match/interest/{interestId}', [AdminMatchController::class, 'matchByInterest']);

    Route::post('/match/manual', [AdminMatchController::class, 'manualMatch']);

    // Donation Execution
    Route::post('/matches/{matchId}/execute', [
        AdminExecutionController::class,
        'executeDonation'
    ]);

    // Match Completion
    Route::post('/matches/{matchId}/complete', [
        AdminExecutionController::class,
        'completeMatch'
    ]);
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

     // Add this line for donors to view approved requests
    Route::get('/requests/approved', [RequestController::class, 'approvedRequests']);

    // interests
    Route::post('/requests/{id}/interest', [InterestController::class, 'store']);
    Route::get('/interests', [InterestController::class, 'myInterests']);
    Route::delete('/interests/{id}', [InterestController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:receiver'])->prefix('receiver')->group(function () {
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests', [RequestController::class, 'myRequests']);
    Route::delete('/requests/{id}', [RequestController::class, 'destroy']);
});


