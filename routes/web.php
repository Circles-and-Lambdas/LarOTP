<?php

use circlesandlambdas\larotp\Http\Controllers\LarOTPController;
use Illuminate\Support\Facades\Route;

Route::get('/verify', [LarOTPController::class, 'index'])->name('verify');
Route::post('/verify-otp', [LarOTPController::class, 'verify'])->name('verify.otp');
Route::middleware('throttle:1,2')->group(function () {
    Route::post('/resend-otp', [LarOTPController::class, 'requestOTP'])->name('resend.otp');
});
