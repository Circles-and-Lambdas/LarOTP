<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp;
use circlesandlambdas\larotp\Models\UserOTP;
use circlesandlambdas\larotp\OTPInterface;
use RuntimeException;

abstract class OTP implements OTPInterface{

    public function generateOTPValue(): string{
        $otp = $this->generateOTP();
        return $otp;
    }

    abstract public function generateOTP(): string;

    public function storeOTP($clientOTP, $user_id){
        if(!isset($clientOTP)){
            throw new RuntimeException("Missing client OTP");
        }

        UserOTP::create([
            'user_id' => $user_id,
            'otp' => bcrypt($clientOTP),
            'expires_at' => now()->addMinutes(config('larotp.expiry_min')),
        ]);
    }
}