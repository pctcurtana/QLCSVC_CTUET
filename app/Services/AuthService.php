<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthService
{
    /**
     * Xử lý đăng nhập
     *
     * @param array $credentials
     * @param bool $remember
     * @param Request $request
     * @return bool
     */
    public function login(array $credentials, bool $remember, Request $request): bool
    {
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return true;
        }

        return false;
    }

    /**
     * Xử lý đăng xuất
     *
     * @param Request $request
     * @return void
     */
    public function logout(Request $request): void
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Lấy user đang đăng nhập
     *
     * @return \App\Models\User|null
     */
    public function getCurrentUser()
    {
        return Auth::user();
    }

    /**
     * Kiểm tra user đã đăng nhập chưa
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return Auth::check();
    }

    /**
     * Lấy ID của user đang đăng nhập
     *
     * @return int|null
     */
    public function getCurrentUserId(): ?int
    {
        return Auth::id();
    }
}

