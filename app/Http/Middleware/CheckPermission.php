<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $screenCode
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $screenCode, $permission = 'can_view')
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin có tất cả quyền
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Kiểm tra quyền
        if (!$user->hasPermission($screenCode, $permission)) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                return back()->with('error', 'Bạn không có quyền truy cập chức năng này!');
            }
            abort(403, 'Bạn không có quyền truy cập chức năng này!');
        }

        return $next($request);
    }
}


