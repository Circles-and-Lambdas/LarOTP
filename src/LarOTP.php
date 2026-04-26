<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp;

use circlesandlambdas\larotp\Models\UserCounter;
use Exception;
use Illuminate\Auth\Authenticatable;
use InvalidArgumentException;
use RuntimeException;

class LarOTP{
    public function __construct(array $config = null){
        if(isset($this->config)){
            throw new RuntimeException("larotp config not set up correctly");
        }
        $this->config = $config ?? config('larotp');
    }

    /**
     * Creates secret key of length in config. The key will be displayed on otp_management.blade.php
     * and should be stored in .env as SECRET_OTP_KEY.
     * @return string
     * @throws InvalidArgumentException If key_length is invalid
     * @throws RuntimeException If random bytes cannot be generated
     * @throws Exception for unexpected errors
     */
    public function createSecretKey(): string{

        try{
            if(!isset($this->config['key_length'])){
                throw new InvalidArgumentException(
                    "Config 'key_length' is not set in larotp config"
                );
            }
            $key_length = $this->config['key_length'];

            if(!is_int($key_length)){
                throw new InvalidArgumentException(
                    "Config 'key_lengh' must be an int. ".gettype($key_length)." given"
                );
            }

            $random_bytes = random_bytes($key_length);
            $converted_key = bin2hex($random_bytes);

            //The key is encoded using base64
            $encoded_key = base64_encode($converted_key);
            $string = $encoded_key;

            return $string;
        }catch(\Throwable $e){
            error_log("Unexpected error in CreateSecretKey: ".$e->getMessage());
            throw new Exception(
                "An unexpected error occurred while generating secret key",
                0,
                $e
            );
        }
    }

    /**
     * Reads decoded key from .env to use for encryption and use during HMAC creation.
     * @return string secret_key
     * @throws InvalidArgumentException if secrect key is not found
     */
    public function getSecretKey(): string{
        try {
            $env_secret_key = $this->config['secret_key'];

            if(isset($env_secret_key)){

                $decoded_key = base64_decode($env_secret_key, true);

                return $decoded_key;
            }else{
                throw new InvalidArgumentException('SYMM_KEY not found on .env file', 0);
            }
        } catch (\Throwable $th) {
            error_log("Unexpected errors. ".$th->getMessage());
            throw new Exception(
                "The code has run into unexpected errors", 
                0,
                $th
            );
        }
    }

    /**
     * Instatiate a new HOTP class
     * @return HOTP
     */
    public function HOTP($user_counter){
        return new HOTP($this->getSecretKey(), $user_counter->counter, $this->config['digits'], $this->config['algo']);
    }

    /**
     * Function that generates the counter used during HOTP ganeration
     * @param Authenticatable $user
     * @return void
     */
    public function createUserCounter(Authenticatable $user){
        
        if(UserCounter::where('user_id', $user->id)->exists()){
            return; 
        }
        
        $min = 100;
        $max = 10000;

        $counter = random_int($min, $max);

        UserCounter::create([
            'user_id' => $user->id,
            'counter' => $counter,
        ]);
    }
}

