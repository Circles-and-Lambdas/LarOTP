<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

class LarOTPController extends Controller
{
    public $verification_route;

    public function __construct()
    {
        $this->middleware('auth');
        $this->verification_route = route('verify.otp');
    }

    public function index(Request $request)
    {
        // Rate Limit access to OTP page
        $this->rateLimitCheck($request);

        $user = $request->user();

        $otp_record = $user->checkOTPRecord();

        if (! $otp_record) {
            $user->generateTOTP();
        }

        $url = $this->verification_route;

        $digits = config('larotp.digits') ?? 6;

        return view('larotp::verify', compact('user', 'url', 'digits'));
    }

    /**
     * Accepts client OTP through request and performs a verification to authenticate the user. Returns error messages to front end
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function verify(Request $request): JsonResponse
    {

        $user = $request->user();
        $client_otp = $request->client_otp;

        if (! isset($user)) {
            Log::error('The user to be authenticated is missing');

            return response()->json([
                'verified' => false,
                'message' => 'User to be autheniticated is missing',
            ]);
        }

        if (! isset($client_otp)) {
            Log::error("The user's OTP to be verified is missing");

            return response()->json([
                'verified' => false,
                'message' => "The user's OTP to be verified is missing",
            ]);
        }

        $verification_output = $user->verifyTOTP($client_otp);

        if ($verification_output['verified']) {
            $url = session()->pull('url.intended');

            $user->updateOTPRecord();

            return response()->json([
                'verified' => $verification_output['verified'],
                'message' => $verification_output['message'],
                'redirect_url' => $url ?? null,
            ]);
        }

        return response()->json([
            'verified' => false,
            'message' => $verification_output['message'],
        ], 422);

    }

    public function requestOTP(Request $request)
    {
        $user = $request->user();

        $otp = $user->generateTOTP();

        if ($otp) {
            return response()->json([
                'otp_request' => true,
                'message' => 'OTP created successfully',
            ]);
        }
    }

    /**
     * Adds a rate limiter to the index function to prevent too many attempts to view the page
     *
     * @param  mixed  $request
     */
    private function rateLimitCheck($request)
    {
        $key = 'verification-attempt: '.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts = 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json(['message' => "Wait $seconds seconds"], 429);
        }

        RateLimiter::hit($key, $decaySeconds = 60);
    }
}
