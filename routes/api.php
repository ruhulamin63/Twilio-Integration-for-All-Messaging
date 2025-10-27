<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Callback endpoints for all platforms (also CSRF exempt)
Route::post('/callback/twilio', [MessageController::class, 'receiveCallback'])->name('callback.twilio')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/callback/whatsapp', [MessageController::class, 'receiveCallback'])->name('callback.whatsapp')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/callback/sms', [MessageController::class, 'receiveCallback'])->name('callback.sms')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/callback/messenger', [MessageController::class, 'receiveCallback'])->name('callback.messenger')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
