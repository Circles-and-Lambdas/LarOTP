<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;

class LarOTPController extends Controller{

    public $verification_route;

    public function __construct(){
        $this->middleware('auth');
        $this->verification_route = route('verify.otp');
    }

    public function index(Request $request){
        $user = $request->user();

        $user->generateHOTP();

        $url = $this->verification_route;

        return view('larotp::verify', compact('user', 'url',));
    }

    /**
     * Accepts client OTP through request and performs a verification to authenticate the user.
     * @param Request $request
     * @throws RuntimeException
     * @return void
     */
    public function verify(Request $request): JsonResponse{
        $user = $request->user();
        $client_otp = $request->client_otp;

        if(!isset($user)){
            throw new RuntimeException("The user to be authenticated is missing");
        }

        if(!isset($client_otp)){
            throw new RuntimeException("The user's OTP to be verified is missing");
        }

        $verification_output = $user->verifyOTP($client_otp);

        dd($verification_output);

        return response()->json([]);
    }

}