<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class UserOTP extends Model{
    protected $table = "user_otps";
    
    use MassPrunable;

    public $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function prunable(){
        return static::where("created_at", "<=", now()->subMonth());
    }
}