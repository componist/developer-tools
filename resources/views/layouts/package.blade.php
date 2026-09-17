<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('favicon/manifest.json') }}">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    <meta name="theme-color" content="#ffffff">

    <title>Developer Tools - Laravel</title>

    @livewireStyles

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    @include('component::components.layouts.partials.theme-boot')

    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    {{-- <link href="https://cdn.jsdelivr.net/npm/enlighterjs@3.4.0/dist/enlighterjs.min.css" rel="stylesheet"> --}}
    <link href="https://cdn.jsdelivr.net/npm/enlighterjs@3.4.0/dist/enlighterjs.dracula.min.css" rel="stylesheet">

</head>

<body
    class="overflow-hidden font-sans transition-colors duration-300 bg-slate-200 text-slate-600 dark:bg-slate-900 dark:text-slate-400"
    x-data>

    <div x-data="{ open: false }" class="flex h-screen ">
        <div :class="open ? 'left-0' : '-left-72'"
            class="fixed top-0 bottom-0 px-5 overflow-y-auto transition-all duration-300 ease-linear bg-white dark:bg-slate-800 w-72 py-7">
            <div class="pb-3 border-b border-gray-200 dark:border-slate-600">
                <div class="flex items-center gap-3">
                    <x:developerTools::logo class="text-white rounded-md w-12 h-12 bg-[#00BBA7]" />
                    <h1 class="text-2xl font-bold text-center text-slate-800 dark:text-slate-100">Componist</h1>
                </div>
            </div>


            <hr class="border-gray-200 dark:border-slate-600" />
            <nav class="px-2 mt-7">
                <ul class="grid grid-cols-1 gap-3">
                    {{-- <li class="my-3" x-data="{ open: false }">
                        <button x-on:click="open = ! open"
                            class="flex items-center justify-between w-full text-slate-600 transition-all duration-200 ease-linear hover:text-teal-500"
                            href="">
                            E-Commerse<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                        <ul x-show="open" x-transition class="px-5 py-3 my-3 bg-teal-500 rounded-md shadow">
                            <li class="my-3">
                                <a class="text-slate-200 transition-all duration-200 ease-linear hover:text-white"
                                    href="">Link</a>
                            </li>
                            <li class="my-3">
                                <a class="text-slate-200 transition-all duration-200 ease-linear hover:text-white"
                                    href="">Link</a>
                            </li>
                        </ul>
                    </li> --}}
                    {{-- <li class="my-3">
                        <a class="text-slate-600 transition-all duration-200 ease-linear hover:text-teal-500"
                            href="">Finance</a>
                    </li> --}}
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.model') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.model') }}">Model</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.validation') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.validation') }}">Validation</a>
                    </li>
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.search.string') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.search.string') }}">Search
                            String</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.create.migration') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.create.migration') }}">Create
                            Migration</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.create.view') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.create.view') }}">Create View</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.database.schema') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.database.schema') }}">Database
                            Schema</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.database.tools') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.database.tools') }}">Database Tools</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.routes') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.routes') }}">Route Liste</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.artisan.list') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.artisan.list') }}">Artisan Liste</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.job.liste') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.job.liste') }}">Job Liste</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.root.icons') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.root.icons') }}">Root Icons</a>
                    </li>




                    <p class="mb-3 text-base text-teal-500 uppercase mt-7">Document</p>
                    <hr class="border-gray-200 dark:border-slate-600" />
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.crud') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.crud') }}">CRUD</a>
                    </li>
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.rest-api') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.rest-api') }}">REST API</a>
                    </li>

                    <p class="mb-3 text-base text-teal-500 uppercase mt-7">Test</p>
                    <hr class="border-gray-200 dark:border-slate-600" />
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.pest-examples') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.pest-examples') }}">Pest Examples</a>
                    </li>

                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.test.routes') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.test.routes') }}">Test Routes</a>
                    </li>


                    <p class="mb-3 text-base text-teal-500 uppercase mt-7">Crawler</p>
                    <hr class="border-gray-200 dark:border-slate-600" />
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.crawler.route.json') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.crawler.route.json') }}">Frontend
                            Route Crawler
                            Json</a>
                    </li>

                    <p class="mb-3 text-base text-teal-500 uppercase mt-7">Setting</p>
                    <hr class="border-gray-200 dark:border-slate-600" />
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.setting.env') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.setting.env') }}">.ENV File</a>
                    </li>
                    <li>
                        <a class="transition-all duration-200 ease-linear hover:text-teal-500 dark:hover:text-teal-400 {{ request()->routeIs('componist.developer-tools.setting.log') ? 'text-teal-500 dark:text-teal-400' : 'text-slate-600 dark:text-slate-400' }}"
                            href="{{ route('componist.developer-tools.setting.log') }}">Laravel Log File</a>
                    </li>

                </ul>
            </nav>
        </div>
        <div :class="open ? 'left-72' : 'left-0'"
            class="relative w-full overflow-y-auto transition-all duration-300 ease-linear bg-slate-100 dark:bg-slate-900">
            <div class="px-5 border-b border-gray-200 dark:border-slate-600 py-7">
                <div class="flex items-center justify-between gap-5">
                    <button x-on:click="open = ! open" type="button"
                        class="hover:text-teal-500 dark:hover:text-teal-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>

                    <button
                        type="button"
                        @click.prevent="$store.theme.toggle()"
                        class="flex items-center gap-2 px-4 py-2 transition hover:text-teal-500 dark:hover:text-teal-400"
                        aria-label="Darstellung umschalten"
                    >
                        <svg x-show="$store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v1m0 16v1m8.49-8.49h1M3.51 12H2.5m15.364 6.364l.707.707M5.636 5.636l-.707-.707m12.728 0l.707-.707M5.636 18.364l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>

                        <svg x-cloak x-show="! $store.theme.dark" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5"
                            fill="currentColor" viewBox="0 0 24 24">
                            <path d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="px-5 py-7">
                <!--start content-->
                {{-- <h2 class="mb-3 text-4xl font-extrabold leading-none text-slate-900 md:text-5xl xl:text-6xl">
                    Lorem ipsum dolor sit amet.
                </h2>
                <p class="max-w-3xl text-base text-slate-600 xl:text-xl">
                    Lorem ipsum dolor, sit amet consectetur adipisicing elit. Libero, atque asperiores non
                    accusamus porro dolor. Omnis, obcaecati. Et est, architecto maiores, beatae sit sed
                    ullam cumque ea blanditiis consequatur sequi.
                </p> --}}

                {{ $slot }}


                <!--end content-->
            </div>
        </div>
    </div>
    <!--dashboard end-->



    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/enlighterjs@3.4.0/dist/enlighterjs.min.js"></script>

    <script>
        EnlighterJS.init('pre', 'code', {
            language: 'javascript,php, html, css',
            theme: 'dracula',
            indent: 2
        });
    </script>
</body>

</html>
