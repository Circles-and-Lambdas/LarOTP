<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp;

interface OTPInterface{
    public function generateOTP(): string;
    public function verify(string $clientOTP);
    public function storeOTP($clientOTP, $user_id);
    public function truncate($hmac);
    public function generateValue($truncated, $digits);
}