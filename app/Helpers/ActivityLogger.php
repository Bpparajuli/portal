<?php

namespace App\Helpers;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($description, $userId = null)
    {
        Activity::create([
            'user_id' => $userId ?? Auth::id(),
            'description' => $description,
        ]);
    }
}
