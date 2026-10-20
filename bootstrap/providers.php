<?php

use App\Providers\AppServiceProvider;
use Barryvdh\Debugbar\ServiceProvider;

return [
    AppServiceProvider::class,
    // App\Providers\TelescopeServiceProvider::class, // Temporarily disabled until fixed
    ServiceProvider::class,
];
