<?php

namespace App\Helpers;

use App\Models\Log;
use Illuminate\Support\Facades\Request;

class LogHelper
{
    public static function log($type, $description = null, $user_id = null, $related_user_id=null)
    {
        $userId = $user_id ?? null;
        Log::create([
            'user_id' => $userId,
            'related_user_id' => $related_user_id,
            'type' => $type,
            'description' => $description,
            'ip_address' => Request::ip(),
        ]);
    }
}
