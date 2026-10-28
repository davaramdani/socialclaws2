<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login() { return view('auth.login', ['admin' => false]); }
    public function register() { return view('auth.register'); }
    public function adminLogin() { return view('auth.login', ['admin' => true]); }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau kata sandi tidak sesuai.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('home'))->with('success', 'Selamat datang kembali di SocialClaws.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:30', 'unique:users,email'],
            'address' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(8)],
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended(route('home'))->with('success', 'Akun berhasil dibuat. Saatnya pilih nail look favoritmu!');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function authenticateAdmin(Request $request)
    {
        $credentials = $request->validate(['username' => ['required', 'string'], 'password' => ['required', 'string']]);
        if (!Auth::guard('admin')->attempt($credentials)) {
            return back()->withErrors(['username' => 'Username atau kata sandi admin tidak sesuai.'])->onlyInput('username');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    public function adminLogout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
