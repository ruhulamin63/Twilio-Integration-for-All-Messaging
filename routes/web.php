<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Messaging Routes
Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
Route::post('/send-sms', [MessageController::class, 'sendSms'])->name('messages.sms');
Route::post('/send-whatsapp', [MessageController::class, 'sendWhatsapp'])->name('messages.whatsapp');
Route::post('/send-messenger', [MessageController::class, 'sendMessenger'])->name('messages.messenger');
Route::post('/send-telegram', [MessageController::class, 'sendTelegram'])->name('messages.telegram');

// Get message history
Route::get('/api/messages', [MessageController::class, 'getMessages'])->name('messages.history');

// Webhook endpoints (no CSRF protection needed)
Route::post('/webhook/twilio', [MessageController::class, 'twilioWebhook'])->name('webhook.twilio')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/webhook/telegram', [MessageController::class, 'telegramWebhook'])->name('webhook.telegram')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);