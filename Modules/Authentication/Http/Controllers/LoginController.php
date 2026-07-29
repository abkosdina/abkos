<?php

namespace Modules\Authentication\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('authentication::auth.login', ['active' => 'options']);
    }

    public function showRegisterForm(): View
    {
        return view('authentication::auth.login', ['active' => 'register']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return Redirect::intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'نام کاربری یا رمز عبور نامعتبر است.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('login');
    }
}
