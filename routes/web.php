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

// Login routes - shows login page with both local and SSO options
Route::middleware('guest')->group(function () {
    // Redirects to our login page if not authenticated
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // This is our own log in page - ideally with an option to log in locally for local/dev - and of course the "Login with SSO" button
    Route::get('/login', [\App\Http\Controllers\Auth\SSOController::class, 'login'])->name('login');
    // Or as a Livewire component if you prefer
    // Route::get('/login', App\Livewire\Login::class)->name('login');
});

// SSO specific routes
Route::post('/login', [\App\Http\Controllers\Auth\SSOController::class, 'localLogin'])->name('login.local');
Route::get('/login/sso', [\App\Http\Controllers\Auth\SSOController::class, 'ssoLogin'])->name('login.sso');
Route::get('/auth/callback', [\App\Http\Controllers\Auth\SSOController::class, 'handleProviderCallback'])->name('sso.callback');
Route::post('/logout', [\App\Http\Controllers\Auth\SSOController::class, 'logout'])->name('auth.logout');
Route::get('/logged-out', [\App\Http\Controllers\Auth\SSOController::class, 'loggedOut'])->name('logged_out');

Route::middleware('auth')->group(function () {
    Route::get('/', \App\Livewire\VmList::class)->name('home');
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'index'])->name('user.index');
    Route::get('/api-keys', [\App\Http\Controllers\ApiKeyController::class, 'index'])->name('api_key.index');
});
