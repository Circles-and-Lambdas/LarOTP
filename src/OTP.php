<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp;
use circlesandlambdas\larotp\Models\UserOTP;
use circlesandlambdas\larotp\OTPInterface;
use InvalidArgumentException;
use RuntimeException;

abstract class OTP implements OTPInterface{

    public function generateOTPValue(): string{
        $otp = $this->generateOTP();
        return $otp;
    }

    abstract public function generateOTP(): string;

    /**
     * Dynamic truncation of the HMAC-SHA-1 value to generate a 4byte dynamic binary code from the 160 bit value.
     * @param string $hmac HMAC-SHA-1 Value
     * @return int 31-bit $truncated value
     * 
     */
    public function truncate($hmac){
        if(!isset($hmac)){
            throw new InvalidArgumentException("Missing Truncation parameters",0);
        }

        $offset = ord($hmac[19]) & 0x0F;
        
        $truncated = ((ord($hmac[$offset]) & 0x7F) << 24) |
                    ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
                    ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
                    (ord($hmac[$offset + 3]) & 0xFF);

        return $truncated;
    }


    /**
     * Performs a modulo operation on the dynamiscally truncated HMAC to the required digits
     * @param mixed $truncated Output of the dynamic truncation
     * @return string OTP Value
     */
    public function generateValue($truncated, $digits){
        $otp_value = $truncated % pow(10, $digits);

        return str_pad((string)$otp_value, $digits, '0', STR_PAD_LEFT);
    }

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