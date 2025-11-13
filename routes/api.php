<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PortfolioController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Keep the default sanctum-protected user route (if used by other parts of app)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public API for listing portfolio items
Route::get('/portfolio', [PortfolioController::class, 'index']);

// Admin and session-based routes need the web middleware so Laravel sessions/cookies work.
Route::middleware(['web'])->group(function () {
    // Session-backed admin endpoints (accessible at /api/admin/*)
    Route::post('/admin/login', [AdminController::class, 'login']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);
    Route::get('/user', [AdminController::class, 'user']);
    
    // Protected admin routes (require admin authentication)
    Route::middleware(['admin.auth'])->group(function () {
        Route::post('/admin/change-password', [AdminController::class, 'changePassword']);
        
        // Portfolio write endpoints (uses session auth via admin session)
        Route::post('/portfolio', [PortfolioController::class, 'store']);
        Route::delete('/portfolio/{id}', [PortfolioController::class, 'destroy']);
    });
});