use App\Http\Controllers\Api\FeedbackController;

Route::middleware('auth:sanctum')->group(function () {

    // Donor feedback
    Route::middleware('role:donor')->prefix('donor')->group(function () {
        Route::post('/feedback', [FeedbackController::class, 'store']);
    });

    // Receiver feedback
    Route::middleware('role:receiver')->prefix('receiver')->group(function () {
        Route::post('/feedback', [FeedbackController::class, 'store']);
    });

    // Admin feedback management
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/feedback', [FeedbackController::class, 'index']);
        Route::get('/feedback/stats', [FeedbackController::class, 'stats']);
        Route::get('/feedback/{id}', [FeedbackController::class, 'show']);
        Route::post('/feedback/{id}/reply', [FeedbackController::class, 'reply']);
        Route::delete('/feedback/{id}', [FeedbackController::class, 'destroy']);
    });

});
