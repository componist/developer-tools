<?php

declare(strict_types=1);

use Componist\DeveloperTools\Http\Controllers\DevController;
use Illuminate\Support\Facades\Route;

if (app()->environment(['local', 'testing'])) {
    Route::middleware(['auth', 'can:manage-developer-tools'])->group(function (): void {
        Route::get('dev', [DevController::class, 'index'])->name('componist.developer-tools.dev.notification');
        Route::get('backup', [DevController::class, 'backup'])->name('componist.developer-tools.dev.backup');
    });
}

$enableDebugUi = app()->environment('testing')
    || (config('app.debug') === true && app()->environment('local'));

if ($enableDebugUi) {
    Route::middleware(['auth', 'can:manage-developer-tools'])
        ->prefix(config('developer-tools.route_prefix', 'componist'))
        ->name('componist.developer-tools.')
        ->group(function (): void {
            Route::view('/', 'developerTools::page.index')->name('index');
            Route::get('/mode', \Componist\DeveloperTools\Livewire\Model::class)->name('model');
            Route::get('/validation', \Componist\DeveloperTools\Livewire\Validation::class)->name('validation');
            Route::get('/search/string', \Componist\DeveloperTools\Livewire\Search\StringInFiles::class)->name('search.string');
            Route::get('/create/migration', \Componist\DeveloperTools\Livewire\Create\Migration::class)->name('create.migration');
            Route::get('/create/view', \Componist\DeveloperTools\Livewire\Create\View::class)->name('create.view');

            Route::get('icons', \Componist\DeveloperTools\Livewire\RootComponents\Index::class)->name('root.icons');

            Route::prefix('database')->name('database.')->group(function (): void {
                Route::get('tools', \Componist\DeveloperTools\Livewire\Database\Tools::class)->name('tools');
                Route::get('schema', \Componist\DeveloperTools\Livewire\Database\Schema::class)->name('schema');
            });

            Route::get('crawler/route', \Componist\DeveloperTools\Livewire\Crawler\RoutesJson::class)->name('crawler.route.json');

            Route::get('artisan/list', \Componist\DeveloperTools\Livewire\Artisan\Liste::class)->name('artisan.list');

            Route::get('job/liste', \Componist\DeveloperTools\Livewire\Jobs\Liste::class)->name('job.liste');

            Route::prefix('test')->name('test.')->group(function (): void {
                Route::get('routes', \Componist\DeveloperTools\Livewire\Test\Routes::class)->name('routes');
            });

            Route::prefix('setting')->name('setting.')->group(function (): void {
                Route::get('env', \Componist\DeveloperTools\Livewire\Setting\EnvFile::class)->name('env');
                Route::get('log', \Componist\DeveloperTools\Livewire\Setting\LogFile::class)->name('log');
            });

            Route::view('crud', 'developerTools::page.crud')->name('crud');
            Route::view('rest-api', 'developerTools::page.rest-api')->name('rest-api');
            Route::view('pest-examples', 'developerTools::page.pest-examples')->name('pest-examples');

            Route::get('routes', function () {
                $routeCollection = Route::getRoutes();

                return view('developerTools::page.routes', compact('routeCollection'));
            })->name('routes');
        });
}
