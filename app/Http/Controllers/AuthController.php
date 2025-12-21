<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    /**
     * @var AuthService
     */
    protected $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Hiển thị form đăng nhập
     *
     * @return \Inertia\Response
     */
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Xử lý đăng nhập
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            if ($this->authService->login($credentials, $remember, $request)) {
                return redirect()->intended('/')->with('success', 'Đăng nhập thành công!');
            }

            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->withInput($request->only('email', 'remember'));
        } catch (\Throwable $e) {
            return back()->withErrors([
                'email' => 'Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại.',
            ])->withInput($request->only('email', 'remember'));
        }
    }

    /**
     * Xử lý đăng xuất
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request);
            return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
        } catch (\Throwable $e) {
            return redirect()->route('login')->with('error', 'Có lỗi xảy ra khi đăng xuất.');
        }
    }
}
