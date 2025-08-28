<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Grab locale from header or fallback
        $locale = $request->header('X-Locale', config('app.locale'));

        // Only allow supported locales
        if (in_array($locale, ['en', 'bg'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
