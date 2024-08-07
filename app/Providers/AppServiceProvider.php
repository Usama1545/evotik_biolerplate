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
        $this->app->singleton('memo', function ($app) {
            return new MemoService();
        });

        Order::observe(OrderObserver::class);

        Request::macro('language', function () {
            $lang = $this->header('language') ?? 'en';

            return Language::all()->pluck('iso_2')->contains($lang) ? $lang : 'en';
        });

        Carbon::setLocale('ar');

        Gate::define('update-order-status', function (User $user, Order $order, ?OrderStatusEnum $status = null) {
            $pass = false;
            switch ($status) {
                case OrderStatusEnum::DELIVERED:
                    $pass = $order->is_seller;
                    break;
                case OrderStatusEnum::APPROVED:
                    $pass = $order->is_buyer;
                    break;
                case OrderStatusEnum::REQUESTED_REVISION:
                    $pass = $order->is_buyer;
                    break;
                case OrderStatusEnum::CANCELLED:
                    $pass = $order->is_buyer || $order->is_seller;
                    break;
                default:
                    $pass = $user->is_admin;
                    break;
            }
            return $pass;
        });


        VerifyEmail::createUrlUsing(function ($notifiable) {
            $path = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ],
                absolute: false
            );

            $url = config('app.front_app_url') . str_replace('api/', '', $path);

            return $url;
        });
    }
}
