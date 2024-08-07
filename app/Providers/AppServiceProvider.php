<?php

namespace App\Providers;

use App\Models\Language;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Request::macro('language', function () {
            $lang = $this->header('language') ?? 'en';

            return Language::all()->pluck('iso_2')->contains($lang) ? $lang : 'en';
        });
    }
}
