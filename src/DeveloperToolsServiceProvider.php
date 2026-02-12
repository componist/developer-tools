<?php

namespace Componist\DeveloperTools;

use Componist\DeveloperTools\Commands\ClearStoragePublic;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class DeveloperToolsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/developer-tools.php', 'developer-tools');

        $this->commands([
            ClearStoragePublic::class,
        ]);

        Route::group(['middleware' => ['web']], function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'developerTools');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/developer-tools.php' => config_path('developer-tools.php'),
            ], 'developer-tools-config');
        }

        // blade componente
        $this->bootBladeComponents();

        // livewire componente
        $this->bootLivewireComponents();
    }

    private function bootBladeComponents(): void
    {
        foreach (config('developer-tools.components', []) as $alias => $component) {
            Blade::component(config('developer-tools.prefix').$alias, $component);
        }
    }

    private function bootLivewireComponents(): void
    {
        foreach (config('developer-tools.livewire', []) as $alias => $component) {
            Livewire::component(config('developer-tools.prefix').$alias, $component);
        }
    }
}
