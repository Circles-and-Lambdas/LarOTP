<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Tests;

use circlesandlambdas\larotp\HOTP;
use circlesandlambdas\larotp\LarOTP;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HOTP::class)]
class HOTPTest extends TestCase
{
    public $config = [
        'key_length' => 32,
        'counter' => 0,
        'digits' => 6,
        'algo' => 'sha1',
    ];

    public function test_hotp_object_is_created()
    {
        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new HOTP($key, $this->config['counter'], $this->config['digits'], $this->config['algo']);

        $this->assertNotNull($hotp);
        $this->assertInstanceOf(HOTP::class, $hotp);
    }

    public function test_otp_is_generated()
    {
        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new HOTP($key, $this->config['counter'], $this->config['digits'], $this->config['algo']);

        $otp = $hotp->generateOTP();

        $this->assertNotNull($otp);
        $this->assertIsString($otp);
    }

    public function test_otp_code_verified_successfully()
    {
        $client_otp = '321432';

        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new HOTP($key, $this->config['counter'], $this->config['digits'], $this->config['algo']);

        $output = $hotp->verify($client_otp);

        $this->assertFalse($output['verified']);
    }
}
