<?php

namespace App\Providers;

use App\Services\Cart\CartService;
use App\Services\Payments\PaymentManager;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PriceCalculator::class);
        $this->app->singleton(CartService::class);
        $this->app->singleton(PaymentManager::class);
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        View::composer('*', function ($view) {
            $view->with('siteCart', app(CartService::class)->current());
        });
    }
}
