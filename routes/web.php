<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// use App\Http\Controllers\testdataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CoSoController;
use App\Http\Controllers\KhuNhaController;
use App\Http\Controllers\PhongController;
use App\Http\Controllers\ThietBiController;
use App\Http\Controllers\LichSuBaoDuongController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome');
// });
// Route::get('/home', [testdataController::class,'testQuery']
// );

// QLCSVC Routes
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Quản lý Cơ sở
Route::resource('co-so', CoSoController::class);

// Quản lý Khu nhà
Route::resource('khu-nha', KhuNhaController::class);

// Quản lý Phòng
Route::resource('phong', PhongController::class);

// Quản lý Thiết bị
Route::resource('thiet-bi', ThietBiController::class);

// Quản lý Lịch sử Bảo dưỡng
Route::resource('lich-su-bao-duong', LichSuBaoDuongController::class);
Route::get('/thiet-bi/{thietBi}/lich-su-bao-duong', [LichSuBaoDuongController::class, 'show'])->name('thiet-bi.lich-su-bao-duong');

Route::get('/antds', function () {
    return Inertia::render('Antds');
});
