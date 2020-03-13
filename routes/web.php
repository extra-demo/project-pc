<?php

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

use App\Http\Controllers\CallbackController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use \Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'showIndex']);

Route::get('/info', [IndexController::class, 'info'])
    ->middleware(\App\Http\Middleware\DemoCheckLoginStatusMiddleware::class)
    ->name('info');

Route::get('/login', [LoginController::class, 'login'])->name('login');

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/callback', [LoginController::class, 'callback']);

Route::post('/webhook', [CallbackController::class, 'webhook']);
