<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NotesController;

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

Route::get('/servers', [\App\Http\Controllers\Api\VmController::class, 'index'])->name('api.server.index');
Route::post('/vms', [\App\Http\Controllers\Api\VmController::class, 'store'])->name('api.vm.store');
Route::post('/vms/delete', [\App\Http\Controllers\Api\VmController::class, 'destroyGuest'])->name('api.guest.delete');
Route::post('/servers/delete', [\App\Http\Controllers\Api\VmController::class, 'destroyServer'])->name('api.server.delete');
Route::post('/server/notes', [\App\Http\Controllers\Api\NotesController::class, 'updateServer'])->name('api.server.notes');
Route::post('/guest/notes', [\App\Http\Controllers\Api\NotesController::class, 'updateGuest'])->name('api.guest.notes');
