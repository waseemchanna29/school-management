<?php

namespace App\Helpers;

use App\Models\Campus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CampusContext
{
    /**
     * Get the currently active campus for the logged-in admin.
     */
    public static function current(): ?Campus
    {
        $campusId = Session::get('active_campus_id');
        if (!$campusId) return null;
        return Campus::find($campusId);
    }

    /**
     * Get campus ID from session.
     */
    public static function id(): ?int
    {
        return Session::get('active_campus_id');
    }

    /**
     * Set active campus in session.
     */
    public static function set(int $campusId): void
    {
        Session::put('active_campus_id', $campusId);
    }

    /**
     * Clear campus context.
     */
    public static function clear(): void
    {
        Session::forget('active_campus_id');
    }

    /**
     * Check if the logged-in admin has access to the given campus.
     */
    public static function adminHasAccess(int $campusId): bool
    {
        if (!Auth::check()) return false;
        $user = Auth::user();
        if ($user->isSuperAdmin()) return true;
        return $user->campuses()->where('campus_id', $campusId)->exists();
    }
}