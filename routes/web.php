<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('auth.login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'doLogin'])->name('auth.do_login');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('auth.logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('user.index');
});
