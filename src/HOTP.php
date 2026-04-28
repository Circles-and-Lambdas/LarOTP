<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp;

use InvalidArgumentException;
use RuntimeException;

final class HOTP extends OTP implements OTPInterface
{
    private $key;

    public $counter;

    public $digits;

    public $algo;

    private $lookahead_window = 5;

    /**
     * Create an instance of the HOTP class object
     *
     * @param  mixed  $key  secret key
     * @param  mixed  $counter  User counter
     * @param  mixed  $digits  digits of OTP Value
     * @param  mixed  $algo  Hashing Algorithm
     */
    public function __construct($key, $counter, $digits, $algo)
    {
        $this->key = $key;
        $this->counter = $counter;
        $this->digits = $digits;
        $this->algo = $algo;
    }

    /**
     * OTP Generation using secret Key and user counter. Returns an OTP value of length config['digits']
     */
    public function generateOTP(): string
    {
        $hmac = $this->generateHMAC($this->key, $this->counter);

        $truncated = $this->truncate($hmac);

        $string = $this->generateValue($truncated, $this->digits);

        return $string;
    }

    /**
     * Generates 160-bits(20-bytes) of binary output using a secret key and the user's counter.
     *
     * @param  mixed  $key
     * @param  mixed  $counter
     * @return string output
     *
     * @throws InvalidArgumentException for missing parameters
     * @throws RuntimeException for hmac output invalid length
     */
    private function generateHMAC($key, $counter)
    {

        if (empty($key)) {
            throw new InvalidArgumentException('Missing key parameter', 0);
        }

        if (is_null($counter)) {
            throw new InvalidArgumentException('Missing counter parameter', 0);
        }

        $counterBinary = pack('N*', 0, $counter);

        if (! is_string($key) || ! is_string($counterBinary)) {
            throw new InvalidArgumentException('Incorrect types. Key: '.gettype($key).' Key must be a string. Counter: '.gettype($counter).' Counter must be an string');
        }

        try {
            $output = hash_hmac($this->algo, $counterBinary, $key, true);

            if (strlen($output) !== 20) {
                throw new RuntimeException('HMAC output length invalid: expected 20 bytes, got '.strlen($output));
            }

            return $output;

        } catch (\Throwable $th) {
            error_log('Unexpected error during HMAC generation: '.$th->getMessage());
            throw $th;
        }
    }

    /**
     * Accepts client OTP and performs a verification using a lookahead window to compensate for anticipated OTP attempts.
     *
     * @param  string  $clientOTP  Client OTP
     * @return array{message: string, verified: bool}
     */
    public function verify(string $clientOTP)
    {
        $output = [];

        if (empty($clientOTP)) {
            return [
                'success' => false,
                'message' => 'OTP cannot be empty',
            ];
        }

        if (! isset($this->counter)) {
            throw new InvalidArgumentException('Missing Client Counter');
        }

        if (strlen($clientOTP) !== $this->digits) {
            return [
                'success' => false,
                'message' => 'Invalid Input',
            ];
        }

        for ($i = $this->counter; $i <= $this->counter + $this->lookahead_window; $i++) {
            if (hash_equals($this->generateOTP(), $clientOTP)) {
                $this->counter++;

                return $output = [
                    'verified' => true,
                    'message' => 'User successfully authenticated',
                ];
            }
        }

        return $output = [
            'verified' => false,
            'message' => 'Error during authentication',
        ];
    }

    public function store($clientOTP, $user_id)
    {
        $this->storeOTP($clientOTP, $user_id);
    }
}
