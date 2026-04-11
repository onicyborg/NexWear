<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CuttingController;
use App\Http\Controllers\SewingController;
use App\Http\Controllers\QcController;

Route::view('/login', 'auth.login')->name('login')->middleware('guest');
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);
    if (Auth::attempt($credentials, true)) {
        $request->session()->regenerate();
        $user = Auth::user();
        $role = $user->role->value ?? (string) $user->role;
        return match ($role) {
            'Admin' => redirect()->intended('/admin'),
            'Cutting' => redirect()->intended('/cutting'),
            'Sewing' => redirect()->intended('/sewing'),
            'QC' => redirect()->intended('/qc'),
            default => redirect()->intended('/'),
        };
    }
    return back()->withErrors(['email' => 'Kredensial tidak valid'])->onlyInput('email');
})->name('login')->middleware('guest');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->middleware('role:Admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    });

    Route::prefix('cutting')->middleware('role:Cutting')->group(function () {
        Route::get('/', [CuttingController::class, 'index'])->name('cutting.dashboard');
    });

    Route::prefix('sewing')->middleware('role:Sewing')->group(function () {
        Route::get('/', [SewingController::class, 'index'])->name('sewing.dashboard');
    });

    Route::prefix('qc')->middleware('role:QC')->group(function () {
        Route::get('/', [QcController::class, 'index'])->name('qc.dashboard');
    });
});
