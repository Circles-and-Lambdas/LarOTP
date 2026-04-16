<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserCounter extends Model{
    use HasFactory;
    use MassPrunable;

    protected $guarded = [];

    public function prunable(){
        return static::where('created_at', '<=', now()->subMonth());
    }
}