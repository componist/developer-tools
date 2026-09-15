<?php

declare(strict_types=1);

namespace Componist\DeveloperTools;

use App\Models\User;
use Componist\DeveloperTools\Commands\ClearStoragePublic;
use Componist\DeveloperTools\Console\CreateCursorProjectRuleCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class DeveloperToolsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/developer-tools.php', 'developer-tools');

        $this->commands([
            ClearStoragePublic::class,
            // CreateCursorProjectRuleCommand::class, // optional: Artisan-Command, aktuell nicht benötigt
        ]);

        Route::group(['middleware' => ['web']], function (): void {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'developerTools');
    }

    public function boot(): void
    {
        Gate::define('manage-developer-tools', function (?User $user): bool {
            if ($user === null) {
                return false;
            }

            $adminIds = config('developer-tools.admin_user_ids', []);

            if ($adminIds !== [] && in_array((int) $user->id, array_map('intval', $adminIds), true)) {
                return true;
            }

            if (Schema::hasColumn('users', 'isAdmin') && (int) ($user->getAttribute('isAdmin') ?? 0) === 1) {
                return true;
            }

            return false;
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/developer-tools.php' => config_path('developer-tools.php'),
            ], 'developer-tools-config');

            $this->publishes([
                __DIR__ . '/../stubs/project.mdc.stub' => base_path('.cursor/rules/project.mdc'),
            ], 'developer-tools-cursor-rule');
        }

        $this->bootBladeComponents();
        $this->bootLivewireComponents();
    }

    private function bootBladeComponents(): void
    {
        foreach (config('developer-tools.components', []) as $alias => $component) {
            Blade::component(config('developer-tools.prefix') . $alias, $component);
        }
    }

    private function bootLivewireComponents(): void
    {
        foreach (config('developer-tools.livewire', []) as $alias => $component) {
            Livewire::component(config('developer-tools.prefix') . $alias, $component);
        }
    }
}
