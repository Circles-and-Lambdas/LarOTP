<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Tests;

use circlesandlambdas\larotp\TOTP;
use circlesandlambdas\larotp\LarOTP;
use PHPUnit\Framework\TestCase;

class TOTPTestCase extends TestCase{

    public $config = [
        'key_length' => 32,
        'timestep' => 30,
        'digits' => 6,
        'algoTOTP' => 'sha512'
    ];

    public function testTOTPObjectIsCreated(){
        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new TOTP($key, $this->config['timestep'], $this->config['digits'], $this->config['algoTOTP']);

        $this->assertNotNull($hotp);
        $this->assertInstanceOf(TOTP::class,$hotp);
    }

    public function testOTPIsGenerated(){
        $larotp = new LarOTP($this->config);

        $key = base64_decode($larotp->createSecretKey());

        $hotp = new TOTP($key, $this->config['timestep'], $this->config['digits'], $this->config['algoTOTP']);

        $otp = $hotp->generateOTP();

        $this->assertNotNull($otp);
        $this->assertIsString($otp);
    }

    // public function testOTPCodeVerifiedSuccessfully(){
    //     $client_otp = "321432";

    //     $larotp = new LarOTP($this->config);

    //     $key = base64_decode($larotp->createSecretKey());

    //     $hotp = new HOTP($key, $this->config['counter'], $this->config['digits'], $this->config['algo']);

    //     $output = $hotp->verify($client_otp);

    //     $this->assertFalse($output['verified']);
    // }
}