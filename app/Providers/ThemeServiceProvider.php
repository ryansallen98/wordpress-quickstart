<?php

namespace App\Providers;

use Roots\Acorn\Sage\SageServiceProvider;
use Illuminate\Support\Facades\View;
use TailwindMerge\TailwindMerge;

class ThemeServiceProvider extends SageServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register parent services
        parent::register();

        // Register TailwindMerge instance
        $this->app->singleton('tw', fn() => TailwindMerge::instance());

        // Register Blade icon components
        $this->app->register(\BladeUI\Icons\BladeIconsServiceProvider::class);
        $this->app->register(\BladeUI\Heroicons\BladeHeroiconsServiceProvider::class);
        $this->app->register(\MallardDuck\LucideIcons\BladeLucideIconsServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Call parent boot method
        parent::boot();

        // Share TailwindMerge instance with views
        View::share('tw', TailwindMerge::instance());
    }
}
