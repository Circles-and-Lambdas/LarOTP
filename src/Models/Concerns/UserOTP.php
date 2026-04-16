<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Models;

use Illuminate\Database\Eloquent\MassPrunable;

class UserOTP{

    use MassPrunable;

    public $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}