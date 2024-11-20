<?php

namespace App\Providers;

use Livewire\Livewire;
use App\Livewire\StudentSearch;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        Livewire::component('student-search', StudentSearch::class);
        Blade::component('components.layout', 'layout');

        if ($this->app->environment('local')) {
            $this->app['config']->set('cache.route', false);
        }
    }
}
