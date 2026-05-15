<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Http\Middleware;

use Carbon\Carbon;
use circlesandlambdas\larotp\Models\UserOTP;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * LarOTPKeeper is incharge of redirection incase user is not authorized. Applied on necessary routes.
 */
class LarOTPKeeper
{
    public function handle(Request $request, Closure $next)
    {

        if (! Auth::check()) {
            return $this->handleUnauthenticated($request);
        }

        $user = $request->user();

        if ($request->routeIs('verify') || $request->routeIs('verify.otp')) {
            return $next($request);
        }

        if($this->isVerifiedInSession($user->id)){
            return $next($request);
        }

        return $this->handleUnverified($request, $user);
    }

    protected function handleUnauthenticated(Request $request){
        if($request->expectsJson()){
            return response()->json([
                'verified' => false,
                'message' => "Authentication required"
            ], 401);
        }

        session(['url.intended' => $request->fullUrl()]);

        return redirect()->guest(route('login'));
    }

    protected function handleUnverified(Request $request, $user){
        if($request->expectsJson()){
            return response()->json([
                'verified'=>false,
                'message' => "OTP Verification required",
                'redirect' => route('larotp.verify')
            ], 403);
        }

        session(['larotp.intended' => $request->fullUrl()]);

        return redirect()->route('verify')->with('info', 'Please verify to continue');
    }

    protected function getUserOTP($user_id){
        return UserOTP::where('user_id', $user_id)->first();
    }

    protected function isVerifiedInSession(int $user_id){
        $session_data = session('larotp.verification');

        if(!$session_data || !is_array($session_data)){
            return false;
        }

        if(($session_data['user_id'] ?? null) !== $user_id){
            Log::warning('LarOTP: User ID mismatch in session', [
                'session_user_id' => $session_data['user_id'] ?? null,
                'current_user_id' => $user_id
            ]);

            $this->clearVerificationSession();
            return false;
        }

        $verified_at = $session_data['verified_at'] ?? null;

        if(!$verified_at){
            return false;
        }

        $verified_time = Carbon::parse($verified_at);
        $expiry_min = config('larotp.expiry_min') ?? 10;

        if($verified_time->addMinutes($expiry_min)->isPast()){
            Log::info('LarOTP: Session verification expired', [
                'user_id' => $user_id,
                'verified_at' => $verified_at,
                'expiry_minutes' => $expiry_min
            ]);

            $this->clearVerificationSession();
            return false;
        }

        return true;
    }

    protected function clearVerificationSession(){
        session()->forget('larotp.verification');
    }
}
