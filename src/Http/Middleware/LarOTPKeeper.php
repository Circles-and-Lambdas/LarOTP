<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Http\Middleware;


use circlesandlambdas\larotp\Models\UserOTP;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * LarOTPKeeper is incharge of redirection incase user is not authorized. Applied on necessary routes.
 */
class LarOTPKeeper{
    public function handle(Request $request, Closure $next){
        
        if(!Auth::check()){
            return redirect()->route('login');
        }

        session()->put('url.intended', $request->fullUrl());

        if ($request->routeIs('verify') || $request->routeIs('verify.otp')) {
            return $next($request);
        }

        $user = $request->user();
        $user_otp = $user->checkOTPRecord();


        if(!isset($user_otp)){
            return redirect()->route('verify');
        }

        $expiry_time = config("larotp.expiry_min", 10);

        if($user_otp->expires_at->isPast()){
            return redirect()->route('verify');
        }

        return $next($request);
    }
}