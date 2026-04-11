<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Cutting\DashboardController as CuttingDashboardController;
use App\Http\Controllers\Sewing\DashboardController as SewingDashboardController;
use App\Http\Controllers\QC\DashboardController as QcDashboardController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('admin')->middleware('role:Admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    });

    Route::prefix('cutting')->middleware('role:Cutting')->group(function () {
        Route::get('/', [CuttingDashboardController::class, 'index'])->name('cutting.dashboard');
    });

    Route::prefix('sewing')->middleware('role:Sewing')->group(function () {
        Route::get('/', [SewingDashboardController::class, 'index'])->name('sewing.dashboard');
    });

    Route::prefix('qc')->middleware('role:QC')->group(function () {
        Route::get('/', [QcDashboardController::class, 'index'])->name('qc.dashboard');
    });
});
