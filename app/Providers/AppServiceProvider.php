<?php

namespace App\Providers;

use App\Helpers\Qs;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * Do not run tenant DB queries (e.g. Qs::getSetting) here — boot runs before
     * InitializeTenancyByDomain. Use the view composer below so queries run after tenancy is set.
     *
     * @return void
     */
    public function boot()
    {
        // Share tenant-aware values only when the view is composed (after middleware, tenancy initialized).
        View::composer('*', function ($view) {
            $tenancy = app()->bound(\Stancl\Tenancy\Tenancy::class) ? app(\Stancl\Tenancy\Tenancy::class) : null;
            $view->with('sysName', ($tenancy && $tenancy->initialized) ? Qs::getSystemName() : 'e-maaree');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
        //
    }
}
