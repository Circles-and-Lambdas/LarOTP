<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Tests;

use circlesandlambdas\larotp\LarOTP;
use PHPUnit\Framework\TestCase;

class LarOTPTestCase extends TestCase
{
    public function test_encoded_key_should_be_created()
    {
        $config = [
            'key_length' => 32,
        ];
        $larOTP = new LarOTP($config);

        $encoded_key = $larOTP->createSecretKey();

        $this->assertNotEmpty($encoded_key);
        $this->assertIsString($encoded_key);
    }

    public function test_encoded_key_should_be_base64()
    {
        $config = [
            'key_length' => 32,
        ];
        $larOTP = new LarOTP($config);

        $encoded_key = $larOTP->createSecretKey();

        $decoded_key = base64_decode($encoded_key, true);

        $this->assertIsString($decoded_key);
    }

    // public function TestHOTPObjectShouldBeCreated(){

    // }
}
