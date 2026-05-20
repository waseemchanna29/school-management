<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampusSelectController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super.dashboard');
        }

        $campuses = $user->campuses()->where('is_active', true)->get();

        if ($campuses->isEmpty()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'You have no assigned campuses. Please contact the Super Admin.');
        }

        return view('admin.campus-select', compact('campuses'));
    }

    public function select(Request $request)
    {
        $request->validate([
            'campus_id' => ['required', 'exists:campuses,id'],
        ]);

        $user = Auth::user();

        if (!$user->isSuperAdmin() && !CampusContext::adminHasAccess($request->campus_id)) {
            return back()->with('error', 'You do not have access to this campus.');
        }

        CampusContext::set((int) $request->campus_id);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Campus selected successfully.');
    }

    public function switchCampus()
    {
        CampusContext::clear();
        return redirect()->route('campus.select');
    }
}