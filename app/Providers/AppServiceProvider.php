<?php

namespace App\Providers;

use App\Enums\OrderStatusEnum;
use App\Models\Language;
use App\Models\Order;
use App\Models\User;
use App\Observers\OrderObserver;
use App\Services\MemoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
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
