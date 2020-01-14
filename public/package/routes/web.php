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

use App\Http\Controllers\PayfortController;

Route::get('/', function () {
    return view('index');
});

Route::post('payfort', [PayfortController::class, 'create']);
Route::match(['GET', 'POST'], 'payfort/tokenization', [PayfortController::class, 'handleTokenResponse']);
Route::match(['GET', 'POST'], 'payfort/response', [PayfortController::class, 'handleResponse']);
Route::match(['GET', 'POST'], 'payfort/callback', [PayfortController::class, 'handleCallback']);
Route::match(['GET', 'POST'], 'payfort/error', [PayfortController::class, 'handleError']);
