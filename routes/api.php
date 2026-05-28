<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Read route is always protected — single known consumer who can update their caller easily.
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/servers', [\App\Http\Controllers\Api\VmController::class, 'index'])->name('api.server.index');
});

// Mutating routes are protected only when the vmstats.api_auth_required flag is on.
// Allows a staged rollout: ship tokens to field admins, then flip the flag.
Route::middleware('api.auth_if_enabled')->group(function () {
    Route::post('/vms', [\App\Http\Controllers\Api\VmController::class, 'store'])->name('api.vm.store');
    Route::post('/vms/delete', [\App\Http\Controllers\Api\VmController::class, 'destroyGuest'])->name('api.guest.delete');
    Route::post('/servers/delete', [\App\Http\Controllers\Api\VmController::class, 'destroyServer'])->name('api.server.delete');
    Route::post('/server/notes', [\App\Http\Controllers\Api\NotesController::class, 'updateServer'])->name('api.server.notes');
    Route::post('/guest/notes', [\App\Http\Controllers\Api\NotesController::class, 'updateGuest'])->name('api.guest.notes');
});
