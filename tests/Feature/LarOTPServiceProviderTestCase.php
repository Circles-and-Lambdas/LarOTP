<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\tests\Feature;

use circlesandlambdas\larotp\LarOTPServiceProvider;
use circlesandlambdas\larotp\tests\LarOTPFeatureTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LarOTPServiceProvider::class)]
class LarOTPServiceProviderTestCase extends LarOTPFeatureTestCase{
    public function test_app_can_access_default_config_values(){
        $key_length = config('larotp.key_length');

        $this->assertNotNull($key_length);
    }
}