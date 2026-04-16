<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Tests;

use circlesandlambdas\larotp\HOTP;
use circlesandlambdas\larotp\LarOTP;
use PHPUnit\Framework\TestCase;

class HOTPTestCase extends TestCase{

    public $config = [
        'key_length' => 32,
        'counter' => 0,
        'digits' => 6,
        'algo' => 'sha1'
    ];

    public function testHOTPObjectIsCreated(){
        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new HOTP($key, $this->config['counter'], $this->config['digits'], $this->config['algo']);

        $this->assertNotNull($hotp);
        $this->assertInstanceOf(HOTP::class,$hotp);
    }

    public function testOTPIsGenerated(){
        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new HOTP($key, $this->config['counter'], $this->config['digits'], $this->config['algo']);

        $otp = $hotp->generateOTP();

        $this->assertNotNull($otp);
        $this->assertIsString($otp);
    }

    public function testOTPCodeVerifiedSuccessfully(){
        $client_otp = "321432";

        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new HOTP($key, $this->config['counter'], $this->config['digits'], $this->config['algo']);

        $output = $hotp->verify($client_otp);

        $this->assertFalse($output['verified']);
    }
}