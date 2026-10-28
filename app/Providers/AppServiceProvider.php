<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $view->with('cartCount', auth()->check() ? (int) \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity') : 0);
        });
        \Illuminate\Pagination\Paginator::defaultView('pagination.default');
    }
}
