<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class loginController extends Controller
{
    public function showLoginForm()
    {
        return view('home.login'); // arahkan ke file Blade login kamu
    }

    /**
     * Memproses login pengguna.
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Cek kredensial
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Redirect berdasarkan peran pengguna
            if ($user->role === 'teacher') {
                return redirect()->route('dashboard.guru')
                    ->with('success', 'Selamat datang, Guru!');
            } elseif ($user->role === 'student') {
                return redirect()->route('dashboard.siswa')
                    ->with('success', 'Selamat datang, Siswa!');
            } else {
                return redirect()->route('home')
                    ->with('info', 'Selamat datang di EvoLevel!');
            }
        }

        // Jika gagal login
        return back()->withErrors([
            'email' => 'Email atau kata sandi salah.',
        ])->onlyInput('email');
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah keluar.');
    }
}
