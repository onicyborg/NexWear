<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Cutting\DashboardController as CuttingDashboardController;
use App\Http\Controllers\Sewing\DashboardController as SewingDashboardController;
use App\Http\Controllers\QC\DashboardController as QcDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\MasterQcKpiController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('admin')->middleware('role:Admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::resource('customers', CustomerController::class)->names('customers');
        Route::resource('master-qc', MasterQcKpiController::class)->names('master-qc');
        Route::resource('orders', OrderController::class)->names('orders');
        Route::resource('users', UserController::class)->names('users');
        Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs.index');
    });

    Route::prefix('cutting')->middleware('role:Cutting')->group(function () {
        Route::get('/', [CuttingDashboardController::class, 'index'])->name('cutting.dashboard');
        Route::post('/start/{order}', [CuttingDashboardController::class, 'startProcess'])->name('cutting.start');
        Route::post('/complete/{order}', [CuttingDashboardController::class, 'completeProcess'])->name('cutting.complete');
        Route::get('/history', [CuttingDashboardController::class, 'history'])->name('cutting.history');
    });

    Route::prefix('sewing')->middleware('role:Sewing')->group(function () {
        Route::get('/', [SewingDashboardController::class, 'index'])->name('sewing.dashboard');
        Route::post('/start/{order}', [SewingDashboardController::class, 'startProcess'])->name('sewing.start');
        Route::post('/complete/{order}', [SewingDashboardController::class, 'completeProcess'])->name('sewing.complete');
        Route::get('/history', [SewingDashboardController::class, 'history'])->name('sewing.history');
    });

    Route::prefix('qc')->middleware('role:QC')->group(function () {
        Route::get('/', [QcDashboardController::class, 'index'])->name('qc.dashboard');
    });
});
