<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\User;
use Illuminate\Http\Request;

class CampusController extends Controller
{
    public function index()
    {
        $campuses = Campus::withCount(['teachers', 'students', 'admins'])->latest()->paginate(15);
        return view('super.campuses.index', compact('campuses'));
    }

    public function create()
    {
        return view('super.campuses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255', 'unique:campuses,name'],
            'city'           => ['required', 'string'],
            'district'       => ['required', 'string'],
            'province'       => ['required', 'string'],
            'address'        => ['required', 'string'],
            'phone'          => ['nullable', 'string'],
            'email'          => ['nullable', 'email'],
            'principal_name' => ['nullable', 'string'],
        ]);

        $campus = Campus::create(array_merge(
            $request->except('_token'),
            ['code' => 'CMP-' . strtoupper(substr(md5(uniqid()), 0, 6))]
        ));

        return redirect()->route('super.campuses.show', $campus)
            ->with('success', "Campus \"{$campus->name}\" created successfully.");
    }

    public function show(Campus $campus)
    {
        $campus->loadCount(['teachers', 'students', 'classes', 'subjects']);
        $campus->load('admins');
        $availableAdmins = User::where('role', 'admin')
            ->whereDoesntHave('campuses', fn($q) => $q->where('campus_id', $campus->id))
            ->get();
        return view('super.campuses.show', compact('campus', 'availableAdmins'));
    }

    public function edit(Campus $campus)
    {
        return view('super.campuses.edit', compact('campus'));
    }

    public function update(Request $request, Campus $campus)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255', 'unique:campuses,name,' . $campus->id],
            'city'           => ['required', 'string'],
            'district'       => ['required', 'string'],
            'province'       => ['required', 'string'],
            'address'        => ['required', 'string'],
            'phone'          => ['nullable', 'string'],
            'email'          => ['nullable', 'email'],
            'principal_name' => ['nullable', 'string'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $campus->update($request->except(['_token', '_method']));

        return redirect()->route('super.campuses.show', $campus)
            ->with('success', 'Campus updated successfully.');
    }

    public function destroy(Campus $campus)
    {
        if ($campus->students()->count() > 0 || $campus->teachers()->count() > 0) {
            return back()->with('error', 'Cannot delete campus with existing students or teachers.');
        }
        $campus->delete();
        return redirect()->route('super.campuses.index')
            ->with('success', 'Campus deleted.');
    }

    public function assignAdmin(Request $request, Campus $campus)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->user_id);

        if (!$user->isAdmin()) {
            return back()->with('error', 'Selected user is not an admin.');
        }

        $campus->admins()->syncWithoutDetaching([$request->user_id]);

        return back()->with('success', "{$user->name} assigned to {$campus->name}.");
    }

    public function removeAdmin(Campus $campus, User $user)
    {
        $campus->admins()->detach($user->id);
        return back()->with('success', "{$user->name} removed from {$campus->name}.");
    }
}