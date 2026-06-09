<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\AcademicYearContext;
use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) return $this->redirectByRole();
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Always clear both contexts on fresh login
            CampusContext::clear();
            AcademicYearContext::clear();

            return $this->redirectByRole();
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials. Please try again.'])
            ->onlyInput('email');
    }

    private function redirectByRole()
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super.dashboard');
        }

        if ($user->isAdmin()) {
            $campuses = $user->campuses()->where('is_active', true)->get();

            // Auto-select campus if only one
            if ($campuses->count() === 1) {
                CampusContext::set($campuses->first()->id);
                return $this->redirectToYearSelection($campuses->first()->id);
            }

            return redirect()->route('campus.select');
        }

        if ($user->isTeacher()) {
            $campusId = $user->teacher?->campus_id;
            return $this->redirectToYearSelection($campusId);
        }

        return redirect()->route('login');
    }

    /**
     * After campus is known, check if we should auto-select academic year
     * or show the year selection screen.
     */
    private function redirectToYearSelection(?int $campusId)
    {
        if (!$campusId) {
            return redirect()->route('academic-year.select');
        }

        $years = AcademicYear::where('campus_id', $campusId)
            ->orderByDesc('start_date')
            ->get();

        // Auto-select if only one year or current year exists
        if ($years->count() === 1) {
            AcademicYearContext::set($years->first()->id);
            return $this->redirectToDashboard();
        }

        $currentYear = $years->firstWhere('is_current', true);
        if ($currentYear) {
            AcademicYearContext::set($currentYear->id);
            return $this->redirectToDashboard();
        }

        return redirect()->route('academic-year.select');
    }

    private function redirectToDashboard()
    {
        $user = Auth::user();
        return $user->isTeacher()
            ? redirect()->route('teacher.dashboard')
            : redirect()->route('admin.dashboard');
    }
}