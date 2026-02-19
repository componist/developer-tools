<?php

use Illuminate\Support\Facades\Route;

if (env('APP_DEBUG') === true) {
    Route::prefix(config('developer-tools.route_prefix', 'componist'))->name('componist.developer-tools.')->group(function (): void {
        Route::view('/', 'developerTools::page.index')->name('index');
        Route::livewire('/mode', \Componist\DeveloperTools\Livewire\Model::class)->name('model');
        Route::livewire('/validation', \Componist\DeveloperTools\Livewire\Validation::class)->name('validation');
        Route::livewire('/search/string', \Componist\DeveloperTools\Livewire\Search\StringInFiles::class)->name('search.string');
        Route::livewire('/create/migration', \Componist\DeveloperTools\Livewire\Create\Migration::class)->name('create.migration');
        Route::livewire('/create/view', \Componist\DeveloperTools\Livewire\Create\View::class)->name('create.view');

        Route::livewire('icons', \Componist\DeveloperTools\Livewire\RootComponents\Index::class)->name('root.icons');

        Route::prefix('database')->name('database.')->group(function (): void {
            Route::livewire('tools', \Componist\DeveloperTools\Livewire\Database\Tools::class)->name('tools');
            Route::livewire('schema', \Componist\DeveloperTools\Livewire\Database\Schema::class)->name('schema');
        });

        Route::livewire('crawler/route', \Componist\DeveloperTools\Livewire\Crawler\RoutesJson::class)->name('crawler.route.json');

        Route::livewire('artisan/list', \Componist\DeveloperTools\Livewire\Artisan\Liste::class)->name('artisan.list');

        Route::livewire('job/liste', \Componist\DeveloperTools\Livewire\Jobs\Liste::class)->name('job.liste');

        Route::prefix('test')->name('test.')->group(function (): void {
            Route::livewire('routes', \Componist\DeveloperTools\Livewire\Test\Routes::class)->name('routes');
        });

        Route::prefix('setting')->name('setting.')->group(function (): void {
            Route::livewire('env', \Componist\DeveloperTools\Livewire\Setting\EnvFile::class)->name('env');
            Route::livewire('log', \Componist\DeveloperTools\Livewire\Setting\LogFile::class)->name('log');
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
