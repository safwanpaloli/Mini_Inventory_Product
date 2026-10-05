<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff', // Default role
        ]);

        Auth::login($user);

        return redirect()->intended('dashboard');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in '.$seconds.' seconds.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (!$user->active) {
                throw ValidationException::withMessages([
                    'email' => ['This account is inactive.'],
                ]);
            }

            if ($user->locked_until && $user->locked_until->isFuture()) {
                throw ValidationException::withMessages([
                    'email' => ['Your account is locked until ' . $user->locked_until->format('H:i') . '. Please try again later.'],
                ]);
            }

            if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                RateLimiter::clear($throttleKey);
                
                $user->update([
                    'failed_attempts' => 0,
                    'locked_until' => null,
                ]);
                
                $request->session()->regenerate();

                return redirect()->intended('dashboard');
            } else {
                RateLimiter::hit($throttleKey);
                
                $attempts = $user->failed_attempts + 1;
                $lockedUntil = null;
                
                if ($attempts >= 5) {
                    $lockedUntil = now()->addMinutes(15);
                }
                
                $user->update([
                    'failed_attempts' => $attempts,
                    'locked_until' => $lockedUntil,
                ]);
            }
        } else {
            RateLimiter::hit($throttleKey);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
