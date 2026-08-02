<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        if (User::query()->count() === 0) {
            return redirect()->route('setup');
        }

        return view('workshophub.auth.login', ['settings' => Setting::map()]);
    }

    /**
     * Unit 35: "Three fields, one door" — email + phone + password,
     * with an optional persistent session.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $validated['email'])->first();
        $phoneMatches = $user && preg_replace('/\D+/', '', (string) $user->phone) === preg_replace('/\D+/', '', $validated['phone']);

        if (! $user || ! $phoneMatches || ! Hash::check($validated['password'], $user->password)) {
            return back()->withErrors(['email' => 'Those owner credentials do not match — all three fields must be right.'])->onlyInput('email', 'phone');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Welcome back, '.$user->name.'.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Signed out of the studio dashboard.');
    }
}
