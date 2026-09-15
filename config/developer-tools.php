<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Route URL Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the URL prefix for all Developer Tools routes.
    | The default is "componist". The tools are then available at /componist.
    | You can change this to any string, e.g. "dev-tools" for /dev-tools.
    |
    */

    'route_prefix' => 'componist',

    'admin_user_ids' => array_values(array_filter(array_map(
        static fn (string $id): int => (int) trim($id),
        explode(',', (string) env('DEVELOPER_TOOLS_ADMIN_IDS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    |
    | Below you reference all components that should be loaded for your app.
    | By default all components from Blade UI Kit are loaded in. You can
    | disable or overwrite any component class or alias that you want.
    |
     */

    'components' => [
        'developerTools::layouts.package' => Componist\DeveloperTools\View\PackageLayout::class,
    ],
    /*
    |--------------------------------------------------------------------------
    | Livewire Components
    |--------------------------------------------------------------------------
    |
    | Below you reference all the Livewire components that should be loaded
    | in. By default all components from Blade UI Kit are loaded in.
    |
     */

    'livewire' => [
        'model' => \Componist\DeveloperTools\Livewire\Model::class,
        'validation' => \Componist\DeveloperTools\Livewire\Validation::class,
        'search.string-in-files' => \Componist\DeveloperTools\Livewire\Search\StringInFiles::class,
        'create.migration' => \Componist\DeveloperTools\Livewire\Create\Migration::class,
        'create.view' => \Componist\DeveloperTools\Livewire\Create\View::class,
        'database.schema' => \Componist\DeveloperTools\Livewire\Database\Schema::class,
        'root-components.index' => \Componist\DeveloperTools\Livewire\RootComponents\Index::class,
        'database.tools' => \Componist\DeveloperTools\Livewire\Database\Tools::class,
        'test.routes' => \Componist\DeveloperTools\Livewire\Test\Routes::class,
        'crawler.routes-json' => \Componist\DeveloperTools\Livewire\Crawler\RoutesJson::class,
        'artisan.liste' => \Componist\DeveloperTools\Livewire\Artisan\Liste::class,
        'jobs.liste' => \Componist\DeveloperTools\Livewire\Jobs\Liste::class,
        'setting.env-file' => \Componist\DeveloperTools\Livewire\Setting\EnvFile::class,
        'setting.log-file' => \Componist\DeveloperTools\Livewire\Setting\LogFile::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Components Prefix
    |--------------------------------------------------------------------------
    |
    | This value will set a prefix for all Blade UI Kit components.
    | By default it's empty. This is useful if you want to avoid
    | collision with components from other libraries.
    |
    | If set with "buk", for example, you can reference components like:
    |
    | <x-buk-easy-mde />
    |
     */

    'prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | Third Party Asset Libraries
    |--------------------------------------------------------------------------
    |
    | These settings hold reference to all third party libraries and
    | asset files served through a CDN. Individual components can require
    | these asset files through their static `$assets` property.
    |
     */

    'assets' => [],

];
