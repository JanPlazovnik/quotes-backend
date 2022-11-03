<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/signup', 'signup');
    Route::post('/logout', 'logout');
    Route::post('/refresh', 'refresh');
});

Route::controller(UserController::class)
    ->prefix('me')
    ->group(function () {
        Route::get('/', 'me');
        Route::post('/myquote', 'postQuote');
        Route::put('/myquote/{id}', 'editQuote');
    });

// Catch-all route for 404
Route::any('/{any}', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Not Found',
    ], 404);
})->where('any', '.*');
