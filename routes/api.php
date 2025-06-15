<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\Auth\AuthController;

Route::post('/register/email', [AuthController::class, 'registerByEmail']);
Route::post('/register/phone', [AuthController::class, 'registerByPhone']);
Route::post('/register/telegram', [AuthController::class, 'registerByTelegram']);
Route::post('/login/email', [AuthController::class, 'loginByEmail']);
Route::post('/login/phone', [AuthController::class, 'loginByPhone']);
Route::post('/login/telegram', [AuthController::class, 'loginByTelegram']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
Route::post('/verify-telegram', [AuthController::class, 'verifyTelegram']);
Route::post('/password/request-reset', [AuthController::class, 'requestPasswordReset']);
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
});
