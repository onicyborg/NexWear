<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        return view('auth.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id . ',id'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('photo')) {
            // delete old photo if exists
            if (!empty($user->photo)) {
                Storage::disk('public')->delete('profile/' . ltrim($user->photo, '/'));
            }
            $name = $request->file('photo')->hashName();
            $request->file('photo')->storeAs('public/profile', $name);
            $user->photo = $name; // store only filename, view composes storage/profile/<name>
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->telepon = $validated['telepon'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui');
    }
}
