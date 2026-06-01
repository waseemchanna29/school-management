<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CampusContext;
use App\Http\Controllers\Controller;
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
            CampusContext::clear(); // always reset campus on fresh login
            return $this->redirectByRole();
        }

        return back()
            ->withErrors(['email' => 'Invalid credentials. Please try again.'])
            ->onlyInput('email');
    }

   private function redirectByRole()
{
    $user = Auth::user();

    if ($user->isSuperAdmin()) return redirect()->route('super.dashboard');
    if ($user->isAdmin())      return redirect()->route('campus.select');
    if ($user->isTeacher())    return redirect()->route('teacher.dashboard');

    return redirect()->route('login');
}
}