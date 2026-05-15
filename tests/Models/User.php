<?php

namespace App\Models;

use circlesandlambdas\larotp\Models\UserOTP;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function userOtp()
    {
        return $this->hasOne(UserOTP::class);
    }
}