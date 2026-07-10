<?php

namespace Modules\Profile\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ProfileServiceProvider extends ServiceProvider
{
    protected string $name = 'Profile';

    public function boot(): void
    {
        View::addLocation(module_path($this->name, 'resources/views'));

        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
