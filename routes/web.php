<?php

use circlesandlambdas\larotp\Http\Controllers\LarOTPController;
use Illuminate\Support\Facades\Route;

Route::get('/verify', [LarOTPController::class, 'index'])->name('verify');
Route::post('/verify-otp', [LarOTPController::class, 'verify'])->name('verify.otp');
Route::get('/resend-otp', [LarOTPController::class, 'requestOTP'])->name('resend.otp');