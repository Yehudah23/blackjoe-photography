<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ContactController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/portfolio', [PortfolioController::class, 'index']);


Route::middleware(['web'])->group(function () {
    
    Route::post('/admin/login', [AdminController::class, 'login']);
    Route::post('/admin/logout', [AdminController::class, 'logout']);
    Route::get('/user', [AdminController::class, 'user']);
    Route::post('/contact', [ContactController::class, 'submit']);
    
  
    Route::middleware(['admin.auth'])->group(function () {
        Route::post('/admin/change-password', [AdminController::class, 'changePassword']);
        
      
        Route::post('/portfolio', [PortfolioController::class, 'store']);
        Route::delete('/portfolio/{id}', [PortfolioController::class, 'destroy']);
    });
});