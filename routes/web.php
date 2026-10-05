<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeValueController;

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('brands', BrandController::class);
        Route::resource('attributes', AttributeController::class);
        Route::resource('attribute-values', AttributeValueController::class)->only(['store', 'destroy']);
        Route::get('/stock', [\App\Http\Controllers\StockController::class, 'index'])->name('stock.index');
        Route::post('/stock/adjust', [\App\Http\Controllers\StockController::class, 'adjust'])->name('stock.adjust');
        
        Route::get('/exports', [\App\Http\Controllers\ExportController::class, 'index'])->name('exports.index');
        Route::post('/exports/start', [\App\Http\Controllers\ExportController::class, 'start'])->name('exports.start');
        Route::get('/exports/status/{id}', [\App\Http\Controllers\ExportController::class, 'status'])->name('exports.status');
        Route::get('/exports/download/{id}', [\App\Http\Controllers\ExportController::class, 'download'])->name('exports.download');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });
});
