<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\tests\Unit;

use circlesandlambdas\larotp\OTP;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OTP::Class)]
class OTPTest extends TestCase{
    protected $truncated = "11001100011110011111011110100100";
    protected $digits = 6;

    public function test_if_truncate_function_will_reject_null_hmac(){
        $stub = $this->getMockForAbstractClass(OTP::class);

        $this->expectException(\InvalidArgumentException::class);
        
        $stub->truncate(null);
    }

    public function test_if_truncate_function_will_reject_empty_hmac(){
        $stub = $this->getMockForAbstractClass(OTP::class);

        $this->expectException(\InvalidArgumentException::class);
        
        $stub->truncate("");
    }

    public function test_if_truncate_function_will_reject_non_string_value(){
        $stub = $this->getMockForAbstractClass(OTP::class);

        $this->expectException(\InvalidArgumentException::class);
        
        $stub->truncate(123);
    }

    public function test_if_generated_value_function_will_reject_null_values(){
        $stub = $this->getMockForAbstractClass(OTP::class);

        $this->expectException(\InvalidArgumentException::class);
        
        $stub->generateValue(null, null);
    }

    public function test_if_generated_value_function_will_reject_nonstring_truncated_value(){
        $stub = $this->getMockForAbstractClass(OTP::class);

        $this->expectException(\InvalidArgumentException::class);
        
        $stub->generateValue(123, 6);
    }

    public function test_if_generated_value_function_will_create_n_digit_value(){
        $stub = $this->getMockForAbstractClass(OTP::class);

        $value = $stub->generateValue($this->truncated, $this->digits);

        $this->assertNotNull($value);
        $this->assertEquals($this->digits, strlen((string)$value));
    }
}