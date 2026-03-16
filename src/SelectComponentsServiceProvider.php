<?php

namespace SelectComponents;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SelectComponentsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'select-components');

        Blade::component('select-components::components.remote-select', 'remote-select');
        Blade::component('select-components::components.offline-select', 'offline-select');
        Blade::component('select-components::components.multi-select', 'multi-select');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/select-components'),
        ], 'views');
    }

    public function register(): void
    {
        // Register bindings or config here if needed.
    }
}
