<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Dashboard Channel — dùng cho realtime thống kê
|--------------------------------------------------------------------------
| Chỉ cho phép user đã đăng nhập và có quyền xem dashboard hoặc thống kê.
| Admin có tất cả quyền nên luôn pass.
*/
Broadcast::channel('dashboard', function ($user) {
    // Admin luôn có quyền
    if ($user->isAdmin()) {
        return true;
    }
    // User thường: phải có quyền xem thống kê
    return $user->hasPermission('thong-ke', 'can_view');
});
