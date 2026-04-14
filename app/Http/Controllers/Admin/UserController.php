<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->orderBy('name')->get();
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:150','unique:users,email'],
            'role' => ['required','string','in:Admin,Cutting,Sewing,QC'],
            'password' => ['required','string','min:8'],
            'photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ];

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('profile', 'public');
            $data['photo'] = $path;
        }

        User::create($data);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required','string','max:100'],
            'email' => ['required','email','max:150','unique:users,email,' . $user->id],
            'role' => ['required','string','in:Admin,Cutting,Sewing,QC'],
            'password' => ['nullable','string','min:8'],
            'photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Handle photo replace
        if ($request->hasFile('photo')) {
            if (!empty($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }
            $path = $request->file('photo')->store('profile', 'public');
            $data['photo'] = $path;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if (!empty($user->photo)) {
            Storage::disk('public')->delete($user->photo);
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
}
