<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // 2. เช็กอีเมล (ต้องเป็นอีเมลเดียวกับที่คุณใช้ใน Middleware นะครับ)
        if ($user->email === 'admin@billiard.com') {

        // ถ้าเป็นแอดมิน ส่งไป แดชบอร์ดแอดมิน
            return redirect()->route('admin.dashboard');
        }

        //ถ้าเป็น User ส่งไปหน้าแดชบอร์ดปกติ
            return redirect()->intended(route('user.dashboard', absolute: false));
        }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
