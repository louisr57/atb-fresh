<?php

namespace App\Providers;

use App\Livewire\StudentSearch;
use App\Models\Registration;
use App\Observers\RegistrationObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Illuminate\Support\Facades\Cache;
use Rinvex\Country\CountryLoader;

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

        Registration::observe(RegistrationObserver::class);

        if ($this->app->environment('local')) {
            $this->app['config']->set('cache.route', false);
        }

        // Cache countries data for 24 hours
        if (!Cache::has('countries')) {
            Cache::put('countries', CountryLoader::countries(), now()->addHours(24));
        }
    }
}
