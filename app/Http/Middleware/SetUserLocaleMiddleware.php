<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['ar', 'en'];

        if (Schema::hasTable('languages')) {
            $supported = Language::pluck('iso_2')->toArray();
        }

        $selected_lang = $request->header('Language') ?? auth()->user()?->language ?? 'en';

        if ($selected_lang && in_array($selected_lang, $supported)) {
            app()->setLocale($selected_lang);
        }

        return $next($request);
    }
}
