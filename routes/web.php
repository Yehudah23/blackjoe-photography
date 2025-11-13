<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/portfolio', [PortfolioController::class, 'index']);

// Admin routes
Route::post('/admin/login', [AdminController::class, 'login']);
Route::post('/admin/logout', [AdminController::class, 'logout']);
Route::get('/user', [AdminController::class, 'user']);

// Protected admin routes
Route::middleware(['admin.auth'])->group(function () {
    Route::post('/portfolio', [PortfolioController::class, 'store']);
    Route::put('/portfolio/{id}', [PortfolioController::class, 'update']);
    Route::patch('/portfolio/{id}', [PortfolioController::class, 'update']);
    Route::delete('/portfolio/{id}', [PortfolioController::class, 'destroy']);
});



