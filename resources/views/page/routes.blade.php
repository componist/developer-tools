@php
    $routesForJs = collect($routeCollection)->map(function ($route) {
        return [
            'method' => $route->methods()[0] ?? '',
            'uri' => $route->uri(),
            'url' => url($route->uri()),
            'name' => $route->getName() ?? '',
            'action' => $route->getActionName(),
        ];
    })->values()->all();
@endphp

<x:miniHelper::layouts.package>
    <div
        x-data="{
            routes: @js($routesForJs),
            filter: '',
            get filteredRoutes() {
                const q = this.filter.trim().toLowerCase();
                if (!q) return this.routes;
                return this.routes.filter(r => {
                    const uri = (r.uri || '').toLowerCase();
                    const name = (r.name || '').toLowerCase();
                    const action = (r.action || '').toLowerCase();
                    return uri.includes(q) || name.includes(q) || action.includes(q);
                });
            }
        }"
    >
        <div class="container px-3 mx-auto my-14">
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <label for="route-filter" class="text-sm font-medium text-slate-600 dark:text-slate-400">Filtern (URL, Name oder Action):</label>
                <input
                    id="route-filter"
                    type="search"
                    x-model="filter"
                    placeholder="z.B. api, login, UserController..."
                    class="rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-500 dark:placeholder-slate-400 px-3 py-2 text-sm shadow-sm focus:border-slate-500 dark:focus:border-slate-500 focus:ring-1 focus:ring-slate-500 dark:focus:ring-slate-500 w-full"
                />
                <span class="text-sm text-slate-500 dark:text-slate-400" x-show="filter">
                    <span x-text="filteredRoutes.length"></span> von <span x-text="routes.length"></span> Routen
                </span>
            </div>

            <table class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 border-collapse">
                <tr class="sticky top-0 text-sm font-bold text-white uppercase bg-slate-600 dark:bg-slate-700">
                    <td class="px-3 py-2 border border-slate-200 dark:border-slate-600 border-collapse text-nowrap">HTTP Method</td>
                    <td class="px-3 py-2 border border-slate-200 dark:border-slate-600 border-collapse text-nowrap">Route</td>
                    <td class="px-3 py-2 border border-slate-200 dark:border-slate-600 border-collapse text-nowrap">Name</td>
                    <td class="px-3 py-2 border border-slate-200 dark:border-slate-600 border-collapse text-nowrap">Class Path</td>
                </tr>
                <tbody class="text-slate-500 dark:text-slate-300" x-ref="tbody">
                    <template x-for="route in filteredRoutes" :key="route.uri + route.method + route.name">
                        <tr class="hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-slate-100">
                            <td class="px-3 py-2 font-bold text-center border border-slate-200 dark:border-slate-600 border-collapse">
                                <span x-show="route.method === 'GET'" class="text-green-500 dark:text-green-400" x-text="route.method"></span>
                                <span x-show="route.method === 'POST'" class="text-orange-500 dark:text-orange-400" x-text="route.method"></span>
                                <span x-show="route.method === 'PUT'" class="text-blue-500 dark:text-blue-400" x-text="route.method"></span>
                                <span x-show="route.method === 'DELETE'" class="text-red-500 dark:text-red-400" x-text="route.method"></span>
                                <span x-show="!['GET','POST','PUT','DELETE'].includes(route.method)" class="dark:text-slate-300" x-text="route.method"></span>
                            </td>
                            <td class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 border-collapse">
                                <a :href="route.url" target="_blank" class="text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-slate-100 hover:underline" x-text="route.uri"></a>
                            </td>
                            <td class="px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 border-collapse" x-text="route.name"></td>
                            <td class="px-3 py-2 border border-slate-200 dark:border-slate-600 border-collapse" x-text="route.action"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</x:miniHelper::layouts.package>
