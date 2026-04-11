<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user()->role);
        }

        return back()->withErrors(['email' => 'Kredensial tidak valid'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function redirectBasedOnRole($role)
    {
        $value = $role instanceof UserRole ? $role->value : (string) $role;
        return match ($value) {
            'Admin' => redirect()->intended('/admin'),
            'Cutting' => redirect()->intended('/cutting'),
            'Sewing' => redirect()->intended('/sewing'),
            'QC' => redirect()->intended('/qc'),
            default => redirect()->intended('/'),
        };
    }
}
