<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Models\Concerns;

use circlesandlambdas\larotp\HOTP;
use circlesandlambdas\larotp\Models\UserCounter;
use circlesandlambdas\larotp\Models\UserOTP;
use Exception;
use Illuminate\Auth\Authenticatable;
use InvalidArgumentException;
use RuntimeException;

trait LarOTP
{
    public $config;

    public function __construct()
    {
        $this->config = config('larotp');
        if (! isset($this->config)) {
            throw new RuntimeException('larotp config not set up correctly');
        }
    }

    /**
     * Creates secret key of length in config. The key will be displayed on otp_management.blade.php
     * and should be stored in .env as SECRET_OTP_KEY.
     *
     * @throws InvalidArgumentException If key_length is invalid
     * @throws RuntimeException If random bytes cannot be generated
     * @throws Exception for unexpected errors
     */
    public function createSecretKey(): string
    {

        try {
            if (! isset($this->config['key_length'])) {
                throw new InvalidArgumentException(
                    "Config 'key_length' is not set in larotp config"
                );
            }
            $key_length = $this->config['key_length'];

            if (! is_int($key_length)) {
                throw new InvalidArgumentException(
                    "Config 'key_lengh' must be an int. ".gettype($key_length).' given'
                );
            }

            $random_bytes = random_bytes($key_length);
            $converted_key = bin2hex($random_bytes);

            // The key is encoded using base64
            $encoded_key = base64_encode($converted_key);
            $string = $encoded_key;

            return $string;
        } catch (\Throwable $e) {
            error_log('Unexpected error in CreateSecretKey: '.$e->getMessage());
            throw new Exception(
                'An unexpected error occurred while generating secret key. '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Reads decoded key from .env to use for encryption and use during HMAC creation.
     *
     * @return string secret_key
     *
     * @throws InvalidArgumentException if secrect key is not found
     */
    public function getSecretKey(): string
    {
        try {
            $env_secret_key = $this->config['secret_key'];

            if (isset($env_secret_key)) {

                $decoded_key = base64_decode($env_secret_key, true);

                return $decoded_key;
            } else {
                throw new InvalidArgumentException('SECRET_OTP_KEY not found on .env file', 0);
            }
        } catch (\Throwable $th) {
            error_log('Unexpected errors. '.$th->getMessage());
            throw new Exception(
                'The code has run into unexpected errors. '.$th->getMessage(),
                0,
                $th
            );
        }
    }

    public function generateHOTP()
    {
        $counter = $this->UserCounter($this);

        $hotp = $this->HOTP($counter);

        $otp = $hotp->generateOTP();

        $hotp->store($otp, $this->id);

        return $otp;
    }

    public function verifyOTP($client_otp)
    {
        $counter = $this->UserCounter($this);

        $hotp = $this->HOTP($counter);

        $output = $hotp->verify($client_otp);

        UserCounter::where('id', $this->id)->increment('counter');

        return $output;
    }

    /**
     * Instatiate a new HOTP class
     *
     * @return HOTP
     */
    public function HOTP($user_counter)
    {
        return new HOTP($this->getSecretKey(), $user_counter, $this->config['digits'], $this->config['algo']);
    }

    /**
     * Function that generates the counter used during HOTP ganeration
     *
     * @param  Authenticatable  $user
     * @return string $counter
     */
    public function UserCounter($user)
    {

        if (UserCounter::where('user_id', $user->id)->exists()) {
            $user_counter = UserCounter::where('user_id', $user->id)->first();

            return $user_counter->counter;
        }

        $min = 100;
        $max = 10000;

        $counter = random_int($min, $max);

        $user_counter = UserCounter::create([
            'user_id' => $user->id,
            'counter' => $counter,
        ]);

        return $user_counter->counter;
    }

    public function checkOTPRecord()
    {

        $user_otp = UserOTP::where('user_id', $this->id)
            ->whereNotNull('verified_at')
            ->latest('verified_at')
            ->first();

        return $user_otp;
    }

    public function updateOTPRecord()
    {
        UserOTP::where('user_id', $this->id)->latest()->first()->update(['verified_at' => now()]);
    }
}
