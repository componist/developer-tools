<div>
    <div x-data="routeTester({{ json_encode($routes) }}, $wire)">

        <div class="container mx-auto">
            <div class="mb-4 space-y-4">
                <div x-show="!isTestingAll" class="space-y-2">
                    <button @click="testAllGetRoutes()" 
                        :disabled="isTestingAll || testingGroup !== null"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Alle GET-Routes testen
                    </button>
                </div>
                <div x-cloak x-show="isTestingAll" wire:ignore class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-blue-900 dark:text-blue-300">
                            Teste Routes... (<span x-text="testedCount"></span>/<span x-text="totalCount"></span>)
                        </span>
                        <span class="text-sm font-medium text-blue-900" x-text="progressPercent + '%'"></span>
                    </div>
                    <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2.5 mb-2">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                            :style="`width: ${progressPercent}%`"></div>
                    </div>
                    <div x-show="currentTestingRoute" class="text-xs text-gray-600 dark:text-slate-400 font-mono">
                        <span class="inline-flex items-center gap-1">
                            <svg class="animate-spin h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Teste: <span x-text="currentTestingRoute"></span>
                        </span>
                    </div>
                </div>
            </div>

            @php
                $groupedRoutes = $this->groupedRoutes;
            @endphp
            
            <div class="space-y-4">
                @foreach ($groupedRoutes as $groupName => $groupRoutes)
                    <x:miniHelper::block>
                        <div class="mb-4">
                            <div class="bg-gray-100 px-4 py-2 font-semibold text-gray-700 uppercase text-sm border-b border-gray-200 rounded-t-lg flex items-center justify-between">
                                <div>
                                    /{{ $groupName === 'root' ? '' : $groupName }}
                                    <span class="text-xs font-normal text-gray-500 dark:text-slate-400 ml-2">
                                        ({{ count($groupRoutes) }} {{ count($groupRoutes) === 1 ? 'Route' : 'Routes' }})
                                    </span>
                                </div>
                                @php
                                    $testableRoutes = collect($groupRoutes)->filter(function($route) {
                                        return $route['method'] === 'GET' && $route['test'] === true && !$route['dynamicUrl'];
                                    });
                                    $testableCount = $testableRoutes->count();
                                @endphp
                                @if ($testableCount > 0)
                                    <div x-cloak x-show="testingGroup === '{{ $groupName }}'" class="flex flex-col gap-2">
                                        <div class="flex items-center gap-3">
                                            <div class="text-xs text-gray-600 dark:text-slate-400">
                                                (<span x-text="getGroupTestedCount('{{ $groupName }}')"></span>/{{ $testableCount }})
                                            </div>
                                            <div class="w-32 bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                                    :style="'width: ' + (getGroupTestedCount('{{ $groupName }}') / {{ $testableCount }} * 100) + '%'"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-cloak x-show="testingGroup !== '{{ $groupName }}'">
                                        <button @click="testGroupRoutes('{{ $groupName }}')" 
                                            :disabled="isTestingAll || (testingGroup !== null && testingGroup !== '{{ $groupName }}')"
                                            class="px-3 py-1 text-xs bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                            Gruppe testen ({{ $testableCount }})
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full table-fixed border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600">
                                            <th class="w-20 px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider text-center">Method</th>
                                            <th class="px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider text-left">URL</th>
                                            <th class="w-32 px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider text-center">Status</th>
                                            <th class="w-24 px-4 py-3 text-xs font-semibold text-gray-700 uppercase tracking-wider text-center">Aktion</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-600">
                                        @foreach ($groupRoutes as $route)
                                            @php
                                                $index = $route['originalIndex'] ?? null;
                                            @endphp
                                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                                                @switch($route['method'])
                                                    @case('GET')
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="inline-flex items-center justify-center w-16 px-2 py-1 text-xs font-semibold text-white bg-green-500 rounded group-hover:bg-green-600 transition-colors">
                                                                {{ $route['method'] }}
                                                            </span>
                                                        </td>
                                                    @break

                                                    @case('POST')
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="inline-flex items-center justify-center w-16 px-2 py-1 text-xs font-semibold text-white bg-blue-500 rounded group-hover:bg-blue-600 transition-colors">
                                                                {{ $route['method'] }}
                                                            </span>
                                                        </td>
                                                    @break

                                                    @case('PUT')
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="inline-flex items-center justify-center w-16 px-2 py-1 text-xs font-semibold text-white bg-orange-500 rounded group-hover:bg-orange-600 transition-colors">
                                                                {{ $route['method'] }}
                                                            </span>
                                                        </td>
                                                    @break

                                                    @case('DELETE')
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="inline-flex items-center justify-center w-16 px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded group-hover:bg-red-600 transition-colors">
                                                                {{ $route['method'] }}
                                                            </span>
                                                        </td>
                                                    @break

                                                    @default
                                                        <td class="px-4 py-3 text-center">
                                                            <span class="inline-flex items-center justify-center w-16 px-2 py-1 text-xs font-semibold text-white bg-slate-500 rounded group-hover:bg-slate-600 transition-colors">
                                                                {{ $route['method'] }}
                                                            </span>
                                                        </td>
                                                    @break
                                                @endswitch

                                                <td class="px-4 py-3 text-sm">
                                                    <a href="{{ $route['fullUrl'] }}" target="_blank"
                                                        class="text-teal-600 dark:text-teal-400 hover:text-teal-800 dark:hover:text-teal-300 hover:underline font-mono text-xs break-all">
                                                        {{ $route['url'] }}
                                                    </a>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($route['status'] === 'pending')
                                                        <span
                                                            class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                            Pending
                                                        </span>
                                                    @elseif ($route['tested'] || $route['status'] !== null)
                                                        @if ($route['status'] === 200)
                                                            <span
                                                                class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                                                                200 OK
                                                            </span>
                                                        @elseif ($route['status'] === 'error')
                                                            <span
                                                                class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                                                title="{{ $route['error'] ?? 'Fehler' }}">
                                                                Fehler
                                                            </span>
                                                        @elseif (is_numeric($route['status']))
                                                            @if ($route['status'] >= 200 && $route['status'] < 300)
                                                                <span
                                                                    class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                                                                    {{ $route['status'] }}
                                                                </span>
                                                            @elseif ($route['status'] >= 300 && $route['status'] < 500)
                                                                <span
                                                                    class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300">
                                                                    {{ $route['status'] }}
                                                                </span>
                                                            @else
                                                                <span
                                                                    class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                                                                    title="{{ $route['error'] ?? '' }}">
                                                                    {{ $route['status'] }}
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="text-gray-400 dark:text-slate-500 text-sm">-</span>
                                                        @endif
                                                    @else
                                                        <span class="text-gray-400 dark:text-slate-500 text-sm">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                @if ($route['method'] === 'GET' && $route['test'] === true && !$route['dynamicUrl'] && $index !== null)
                                                    <button @click="testSingleRoute({{ $index }})"
                                                        :disabled="isTestingAll || testingGroup !== null"
                                                        class="px-3 py-1.5 text-xs font-medium bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                        Testen
                                                    </button>
                                                    @else
                                                        <span class="text-gray-400 dark:text-slate-500 text-sm">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </x:miniHelper::block>
                @endforeach
            </div>
        </div>

    </div>
</div>

<script>
    function routeTester(routes, wire) {
        return {
            routes: routes,
            isTestingAll: false,
            testedCount: 0,
            totalCount: 0,
            progressPercent: 0,
            currentTestingRoute: null,
            testingGroup: null,
            
            init() {
                // Sync routes with Livewire when they update, but only when not testing
                this.$watch('$wire.routes', (newRoutes) => {
                    if (!this.isTestingAll && this.testingGroup === null) {
                        this.routes = newRoutes;
                    }
                }, { deep: true });
            },
            
            async testRoute(route) {
                // Set pending status before testing
                wire.updateRouteStatus(route.originalIndex, null, null, true);
                
                // Small delay to show pending status
                await new Promise(resolve => setTimeout(resolve, 50));
                
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 10000);
                    
                    const response = await fetch(route.fullUrl, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        signal: controller.signal
                    });
                    
                    clearTimeout(timeoutId);
                    
                    // Always save the status code, even if it's an error status
                    const status = response.status;
                    wire.updateRouteStatus(route.originalIndex, status, null, false);
                } catch (error) {
                    // Only set error if it's a network error, not an HTTP error
                    if (error.name === 'AbortError') {
                        wire.updateRouteStatus(route.originalIndex, null, 'Timeout', false);
                    } else {
                        wire.updateRouteStatus(route.originalIndex, null, error.message || 'Fehler', false);
                    }
                }
            },
            
            async testAllGetRoutes() {
                // Get grouped routes from Livewire to maintain visual order
                const groupedRoutes = @js($this->groupedRoutes);
                
                // Collect all testable routes in the exact order they appear in the view (group by group, top to bottom)
                const testableRoutes = [];
                
                // Iterate through groups in the order they appear
                for (const [groupName, groupRoutes] of Object.entries(groupedRoutes)) {
                    // Iterate through routes in each group in order
                    for (const route of groupRoutes) {
                        if (route.method === 'GET' && 
                            route.test === true && 
                            !route.dynamicUrl &&
                            route.originalIndex !== undefined) {
                            testableRoutes.push({
                                ...route,
                                originalIndex: route.originalIndex
                            });
                        }
                    }
                }
                
                if (testableRoutes.length === 0) {
                    return;
                }
                
                this.isTestingAll = true;
                this.totalCount = testableRoutes.length;
                this.testedCount = 0;
                this.progressPercent = 0;
                this.currentTestingRoute = null;
                
                // Reset all routes
                testableRoutes.forEach(route => {
                    wire.updateRouteStatus(route.originalIndex, null, null, false);
                });
                
                // Test each route sequentially in the exact visual order (from top to bottom)
                for (let i = 0; i < testableRoutes.length; i++) {
                    const route = testableRoutes[i];
                    this.currentTestingRoute = route.url;
                    
                    await this.testRoute(route);
                    
                    // Update progress after testing
                    this.testedCount = i + 1;
                    this.progressPercent = Math.round((this.testedCount / this.totalCount) * 100);
                    
                    // Small delay to prevent overwhelming
                    await new Promise(resolve => setTimeout(resolve, 100));
                }
                
                this.isTestingAll = false;
                this.currentTestingRoute = null;
            },
            
            async testGroupRoutes(groupName) {
                if (this.isTestingAll || (this.testingGroup !== null && this.testingGroup !== groupName)) {
                    return;
                }
                
                // Get grouped routes from Livewire to maintain visual order
                const groupedRoutes = @js($this->groupedRoutes);
                
                // Get routes for this specific group in the exact order they appear
                const groupRoutes = groupedRoutes[groupName] || [];
                
                // Filter testable routes in the exact order they appear in the view
                const testableRoutes = groupRoutes
                    .filter(route => 
                        route.method === 'GET' && 
                        route.test === true && 
                        !route.dynamicUrl &&
                        route.originalIndex !== undefined
                    );
                
                if (testableRoutes.length === 0) {
                    return;
                }
                
                this.testingGroup = groupName;
                
                // Reset all routes in this group
                testableRoutes.forEach(route => {
                    wire.updateRouteStatus(route.originalIndex, null, null, false);
                });
                
                // Test each route sequentially in the exact visual order (from top to bottom)
                for (const route of testableRoutes) {
                    await this.testRoute(route);
                    // Small delay
                    await new Promise(resolve => setTimeout(resolve, 100));
                }
                
                this.testingGroup = null;
            },
            
            async testSingleRoute(index) {
                if (this.isTestingAll || this.testingGroup !== null) {
                    return;
                }
                
                const route = this.routes[index];
                if (!route || route.method !== 'GET' || !route.test || route.dynamicUrl) {
                    return;
                }
                
                await this.testRoute({
                    ...route,
                    originalIndex: index
                });
            },
            
            getGroupTestedCount(groupName) {
                return this.routes
                    .map((route, index) => ({ ...route, originalIndex: index }))
                    .filter(route => {
                        const url = route.url.trim().replace(/^\//, '');
                        const parts = url ? url.split('/') : [];
                        const routeGroup = parts[0] || 'root';
                        return routeGroup === groupName &&
                               route.method === 'GET' && 
                               route.test === true && 
                               !route.dynamicUrl &&
                               (route.tested === true || route.status !== null);
                    }).length;
            }
        }
    }
</script>
