<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Tests;

use circlesandlambdas\larotp\LarOTP;
use circlesandlambdas\larotp\TOTP;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TOTP::class)]
class TOTPTest extends TestCase
{
    protected function createTOTP()
    {
        $config = [
            'key_length' => 32,
            'timestep' => 30,
            'digits' => 6,
            'algoTOTP' => 'sha512',
        ];

        $larotp = new LarOTP($config);

        $key = base64_decode($larotp->createSecretKey());

        $totp = new TOTP($key, $config['timestep'], $config['digits'], $config['algoTOTP']);

        return $totp;
    }

    public function test_totp_object_is_created()
    {
        $totp = $this->createTOTP();

        $this->assertNotNull($totp);
        $this->assertInstanceOf(TOTP::class, $totp);
    }

    public function test_otp_is_generated()
    {
        $totp = $this->createTOTP();
        $otp = $totp->generateOTP();

        $this->assertNotNull($otp);
        $this->assertIsString($otp);
    }

    public function test_otp_code_verified_successfully()
    {
        $totp = $this->createTOTP();
        $currentOTP = $totp->generateOTP();

        $output = $totp->verify($currentOTP);

        $this->assertTrue($output['verified']);
        $this->assertEquals('User successfully authenticated', $output['message']);
    }

    public function test_rejection_of_invalid_otp()
    {
        $totp = $this->createTOTP();

        $output = $totp->verify('000000');

        $this->assertFalse($output['verified']);
        $this->assertEquals('Error during authentication', $output['message']);
    }

    public function test_rejection_of_empty_otp()
    {
        $totp = $this->createTOTP();

        $output = $totp->verify('');

        $this->assertFalse($output['verified']);
        $this->assertEquals('OTP cannot be empty', $output['message']);
    }

    public function test_rejection_of_extra_length_otp()
    {
        $totp = $this->createTOTP();

        $output = $totp->verify('1234567');

        $this->assertFalse($output['verified']);
        $this->assertEquals('Invalid OTP length', $output['message']);
    }

    public function it_uses_constant_time_comparison()
    {
        $totp = $this->createTOTP();

        $validOTP = $totp->generateOTP();

        $start1 = microtime(true);
        $totp->verify($validOTP);
        $time1 = microtime(true) - $start1;

        $start2 = microtime(true);
        $totp->verify('000000');
        $time2 = microtime(true) - $start2;

        $this->assertEqualsWithDelta($time1, $time2, 0.001);
    }
}
