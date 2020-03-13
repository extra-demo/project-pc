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

Route::get('/', [IndexController::class, 'showIndex'])->middleware(\App\Http\Middleware\DemoCheckLoginStatusMiddleware::class);

Route::get('/login', [LoginController::class, 'login']);

Route::get('/callback', [LoginController::class, 'callback']);

Route::get('/webhook', [CallbackController::class, 'webhook']);
