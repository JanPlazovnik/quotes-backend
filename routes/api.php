<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuoteController;
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
        Route::put('/update-password', 'updatePassword');
    });

Route::controller(QuoteController::class)
    ->prefix('quotes')
    ->group(function () {
        Route::get('/', 'getAllQuotes');
        Route::post('/', 'postQuote');
        Route::get('/random', 'getRandomQuote');
        Route::get('/{id}', 'getQuote');
        Route::put('/{id}', 'editQuote');
        Route::delete('/{id}', 'deleteQuote');
        Route::post('/{id}/{type}', 'voteQuote');
    });

// Catch-all route for 404
Route::any('/{any}', function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Not Found',
    ], 404);
})->where('any', '.*');
