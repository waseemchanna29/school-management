<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')
            ->with('campuses')
            ->latest()
            ->paginate(15);
        return view('super.admins.index', compact('admins'));
    }

    public function create()
    {
        $campuses = Campus::where('is_active', true)->orderBy('name')->get();
        return view('super.admins.create', compact('campuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email'],
            'password'  => ['required', 'confirmed', Password::min(8)],
            'campuses'  => ['nullable', 'array'],
            'campuses.*'=> ['exists:campuses,id'],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
        ]);

        if ($request->filled('campuses')) {
            $user->campuses()->sync($request->campuses);
        }

        return redirect()->route('super.admins.index')
            ->with('success', "Admin \"{$user->name}\" created successfully.");
    }

    public function edit(User $user)
    {
        $campuses = Campus::where('is_active', true)->orderBy('name')->get();
        $user->load('campuses');
        return view('super.admins.edit', compact('user', 'campuses'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', 'confirmed', Password::min(8)],
            'campuses'  => ['nullable', 'array'],
            'campuses.*'=> ['exists:campuses,id'],
        ]);

        $user->update([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
        ]);

        $user->campuses()->sync($request->campuses ?? []);

        return redirect()->route('super.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->campuses()->detach();
        $user->delete();
        return redirect()->route('super.admins.index')
            ->with('success', 'Admin removed.');
    }
}