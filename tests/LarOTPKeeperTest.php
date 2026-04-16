<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Tests;

use circlesandlambdas\larotp\Http\Middleware\LarOTPKeeper;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;


class LarOTPKeeperTest extends TestCase{

    public function testRedirectToLogin(){
        $request = new Request();

        (new LarOTPKeeper())->handle($request, function($request){
            $this->assertEquals($request->status(), 302);
        });
    }
}