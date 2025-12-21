<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoSoController;
use App\Http\Controllers\KhuNhaController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ThietBiController;
use App\Http\Controllers\LichSuBaoDuongController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Auth Routes - Guest only
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Logout - Auth only
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== Quản lý Cơ sở ====================
    // Routes create phải đặt TRƯỚC routes có parameter {id}
    Route::middleware('permission:co-so,can_create')->group(function () {
        Route::get('/co-so/create', [CoSoController::class, 'create'])->name('co-so.create');
        Route::post('/co-so', [CoSoController::class, 'store'])->name('co-so.store');
    });
    Route::middleware('permission:co-so,can_view')->group(function () {
        Route::get('/co-so', [CoSoController::class, 'index'])->name('co-so.index');
    });
    Route::middleware('permission:co-so,can_edit')->group(function () {
        Route::get('/co-so/{co_so}/edit', [CoSoController::class, 'edit'])->name('co-so.edit');
        Route::put('/co-so/{co_so}', [CoSoController::class, 'update'])->name('co-so.update');
    });
    Route::middleware('permission:co-so,can_delete')->group(function () {
        Route::delete('/co-so/{co_so}', [CoSoController::class, 'destroy'])->name('co-so.destroy');
    });

    // ==================== Quản lý Khu nhà ====================
    Route::middleware('permission:khu-nha,can_create')->group(function () {
        Route::get('/khu-nha/create', [KhuNhaController::class, 'create'])->name('khu-nha.create');
        Route::post('/khu-nha', [KhuNhaController::class, 'store'])->name('khu-nha.store');
    });
    Route::middleware('permission:khu-nha,can_view')->group(function () {
        Route::get('/khu-nha', [KhuNhaController::class, 'index'])->name('khu-nha.index');
    });
    Route::middleware('permission:khu-nha,can_edit')->group(function () {
        Route::get('/khu-nha/{khu_nha}/edit', [KhuNhaController::class, 'edit'])->name('khu-nha.edit');
        Route::put('/khu-nha/{khu_nha}', [KhuNhaController::class, 'update'])->name('khu-nha.update');
    });
    Route::middleware('permission:khu-nha,can_delete')->group(function () {
        Route::delete('/khu-nha/{khu_nha}', [KhuNhaController::class, 'destroy'])->name('khu-nha.destroy');
    });

    // ==================== Quản lý Phòng ====================
    Route::middleware('permission:phong,can_create')->group(function () {
        Route::get('/phong/create', [PhongController::class, 'create'])->name('phong.create');
        Route::post('/phong', [PhongController::class, 'store'])->name('phong.store');
    });
    Route::middleware('permission:phong,can_view')->group(function () {
        Route::get('/phong', [PhongController::class, 'index'])->name('phong.index');
    });
    Route::middleware('permission:phong,can_edit')->group(function () {
        Route::get('/phong/{phong}/edit', [PhongController::class, 'edit'])->name('phong.edit');
        Route::put('/phong/{phong}', [PhongController::class, 'update'])->name('phong.update');
    });
    Route::middleware('permission:phong,can_delete')->group(function () {
        Route::delete('/phong/{phong}', [PhongController::class, 'destroy'])->name('phong.destroy');
    });

    // ==================== Quản lý Thiết bị ====================
    Route::middleware('permission:thiet-bi,can_create')->group(function () {
        Route::get('/thiet-bi/create', [ThietBiController::class, 'create'])->name('thiet-bi.create');
        Route::post('/thiet-bi', [ThietBiController::class, 'store'])->name('thiet-bi.store');
        Route::get('/thiet-bi/{thiet_bi}/duplicate', [ThietBiController::class, 'duplicate'])->name('thiet-bi.duplicate');
    });
    Route::middleware('permission:thiet-bi,can_view')->group(function () {
        Route::get('/thiet-bi', [ThietBiController::class, 'index'])->name('thiet-bi.index');
        Route::get('/thiet-bi-theo-phong', [ThietBiController::class, 'indexByPhong'])->name('thiet-bi.by-phong');
    });
    Route::middleware('permission:thiet-bi,can_edit')->group(function () {
        Route::get('/thiet-bi/{thiet_bi}/edit', [ThietBiController::class, 'edit'])->name('thiet-bi.edit');
        Route::put('/thiet-bi/{thiet_bi}', [ThietBiController::class, 'update'])->name('thiet-bi.update');
    });
    Route::middleware('permission:thiet-bi,can_delete')->group(function () {
        Route::delete('/thiet-bi/{thiet_bi}', [ThietBiController::class, 'destroy'])->name('thiet-bi.destroy');
    });

    // ==================== Quản lý Lịch sử Bảo dưỡng ====================
    Route::middleware('permission:lich-su-bao-duong,can_create')->group(function () {
        Route::get('/lich-su-bao-duong/create', [LichSuBaoDuongController::class, 'create'])->name('lich-su-bao-duong.create');
        Route::post('/lich-su-bao-duong', [LichSuBaoDuongController::class, 'store'])->name('lich-su-bao-duong.store');
    });
    Route::middleware('permission:lich-su-bao-duong,can_view')->group(function () {
        Route::get('/lich-su-bao-duong', [LichSuBaoDuongController::class, 'index'])->name('lich-su-bao-duong.index');
Route::get('/thiet-bi/{thietBi}/lich-su-bao-duong', [LichSuBaoDuongController::class, 'show'])->name('thiet-bi.lich-su-bao-duong');
    });
    Route::middleware('permission:lich-su-bao-duong,can_edit')->group(function () {
        Route::get('/lich-su-bao-duong/{lich_su_bao_duong}/edit', [LichSuBaoDuongController::class, 'edit'])->name('lich-su-bao-duong.edit');
        Route::put('/lich-su-bao-duong/{lich_su_bao_duong}', [LichSuBaoDuongController::class, 'update'])->name('lich-su-bao-duong.update');
    });
    Route::middleware('permission:lich-su-bao-duong,can_delete')->group(function () {
        Route::delete('/lich-su-bao-duong/{lich_su_bao_duong}', [LichSuBaoDuongController::class, 'destroy'])->name('lich-su-bao-duong.destroy');
    });

    // ==================== Quản lý Người dùng ====================
    Route::middleware('permission:nguoi-dung,can_create')->group(function () {
        Route::get('/nguoi-dung/create', [UserController::class, 'create'])->name('nguoi-dung.create');
        Route::post('/nguoi-dung', [UserController::class, 'store'])->name('nguoi-dung.store');
    });
    Route::middleware('permission:nguoi-dung,can_view')->group(function () {
        Route::get('/nguoi-dung', [UserController::class, 'index'])->name('nguoi-dung.index');
    });
    Route::middleware('permission:nguoi-dung,can_edit')->group(function () {
        Route::get('/nguoi-dung/{nguoi_dung}/edit', [UserController::class, 'edit'])->name('nguoi-dung.edit');
        Route::put('/nguoi-dung/{nguoi_dung}', [UserController::class, 'update'])->name('nguoi-dung.update');
    });
    Route::middleware('permission:nguoi-dung,can_delete')->group(function () {
        Route::delete('/nguoi-dung/{nguoi_dung}', [UserController::class, 'destroy'])->name('nguoi-dung.destroy');
    });

    // ==================== Phân quyền ====================
    Route::middleware('permission:phan-quyen,can_view')->group(function () {
        Route::get('/phan-quyen', [PermissionController::class, 'index'])->name('phan-quyen.index');
        Route::get('/phan-quyen/{user}/permissions', [PermissionController::class, 'getUserPermissions'])->name('phan-quyen.get');
    });
    Route::middleware('permission:phan-quyen,can_edit')->group(function () {
        Route::post('/phan-quyen/{user}/permissions', [PermissionController::class, 'updateUserPermissions'])->name('phan-quyen.update');
    });
});
