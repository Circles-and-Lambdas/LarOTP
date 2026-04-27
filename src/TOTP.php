<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp;

date_default_timezone_set('UTC');

use InvalidArgumentException;
use RuntimeException;

class TOTP extends OTP implements OTPInterface
{
    private $key;

    public $timestep;

    public $digits;

    public $algo;

    public $unix_start_time = 0;

    private $lookahead_window = 5;

    public function __construct($key, $timestep, $digits, $algo)
    {
        $this->key = $key;
        $this->timestep = $timestep;
        $this->digits = $digits;
        $this->algo = $algo;
    }

    public function generateOTP(): string
    {
        $hmac = $this->generateHMAC($this->key);

        $truncated = $this->truncate($hmac);

        $string = $this->generateValue($truncated, $this->digits);

        return $string;
    }

    private function generateHMAC($key)
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Missing key parameter', 0);
        }

        $time = $this->generateTime();

        if (is_null($time)) {
            throw new InvalidArgumentException('Missing time parameter', 0);
        }

        $timeBinary = pack('N*', 0, $time);

        if (! is_string($key) || ! is_string($timeBinary)) {
            throw new InvalidArgumentException('Incorrect types. Key: '.gettype($key).' Key must be a string. Time: '.gettype($timeBinary).' Time must be an string');
        }

        try {
            $output = hash_hmac($this->algo, $timeBinary, $key, true);

            if (strlen($output) !== 64) {
                throw new RuntimeException('HMAC output length invalid: expected 20 bytes, got '.strlen($output));
            }

            return $output;

        } catch (\Throwable $th) {
            error_log('Unexpected error during HMAC generation: '.$th->getMessage());
            throw $th;
        }
    }

    private function generateTime(): float
    {
        if (! isset($this->timestep)) {
            throw new InvalidArgumentException('Missing Time step. Check if time step is initialized');
        }

        $current_unix_time = time();

        $time = floor(($current_unix_time - $this->unix_start_time) / $this->timestep);

        return $time;
    }

    public function verify(string $clientOTP) {}
}
