<div>
    <div class="container mx-auto">
        <div x-data=""
            @crawler-loop.window="
            if ($wire.entangle('running').live) {
                $wire.crawlStep().then(() => {
                    if ($wire.running) {
                        setTimeout(() => window.dispatchEvent(new Event('crawler-loop')), 300);
                    }
                });
            }
        ">
            <div class="mb-4">
                <div class="relative">
                    <x:miniHelper::form.input type="text" wire:model.live='baseUrl' placeholder="{{ url('/') }}"
                        class="text-teal-500" />
                    @if ($baseUrl === null || empty($baseUrl))
                        <button type="button" class="absolute w-5 h-5 top-2 right-4 text-slate-300 dark:text-slate-500 hover:text-teal-500 dark:hover:text-teal-400"
                            @click.prevent="$wire.set('baseUrl','{{ url('/') }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path>
                                <line x1="12" y1="2" x2="12" y2="12"></line>
                            </svg>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-5 mt-7">
                    <button wire:click="startCrawler" class="px-4 py-2 text-white bg-blue-500 rounded">
                        Start Crawling
                    </button>

                    <button wire:click="clearJonsFile" class="px-4 py-2 text-white bg-red-500 rounded">
                        Clear Json File
                    </button>

                    <button wire:click="resetCrawler" class="px-4 py-2 text-white bg-orange-500 rounded">Stop
                        Crawler</button>

                    <button wire:click="generateSitemap" class="px-4 py-2 text-white bg-green-500 rounded">
                        Sitemap erstellen
                    </button>
                </div>
            </div>


            <div class="mb-7">
                @if ($content['running'])
                    <script>
                        window.dispatchEvent(new Event('crawler-loop'));
                    </script>

                    <div class="mb-4 text-slate-600 dark:text-slate-400">
                        Fortschritt: {{ $content['done'] }} / {{ $content['total'] }}
                    </div>

                    <div class="text-blue-500 animate-pulse">Crawler läuft...</div>
                @else
                    {{-- <div class="text-slate-500">Crawler gestoppt</div> --}}
                @endif
            </div>

            <x:miniHelper::block>
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                            <th class="px-5 py-2 text-left">URL</th>
                            <th class="px-5 py-2 text-center">Status</th>
                            <th class="px-5 py-2 text-center">Besucht</th>
                            <th class="px-5 py-2 text-center">Can visit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($content['urls'] as $url)
                            <tr>
                                <td class="px-5 py-2 text-left">
                                    <a href="{{ $url['url'] }}" target="_blank"
                                        class="hover:text-teal-500">{{ $url['url'] }}</a>
                                </td>
                                <td class="px-5 py-2 text-sm text-center">

                                    @if ($url['status_code'] === 200)
                                        <span
                                            class="px-3 py-1 text-white bg-green-500 rounded-md">{{ $url['status_code'] }}</span>
                                    @elseif($url['status_code'] === null)
                                        -
                                    @elseif($url['status_code'] === 'tel')
                                        <span class="px-3 py-1 text-white uppercase bg-blue-500 rounded-md">Tel</span>
                                    @elseif($url['status_code'] === 'mail')
                                        <span class="px-3 py-1 text-white uppercase bg-blue-500 rounded-md">Mail</span>
                                    @else
                                        <span
                                            class="px-3 py-1 text-white bg-red-500 rounded-md">{{ $url['status_code'] }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-2 text-center">{{ $url['visited'] ? '✔️' : '❌' }}</td>
                                <td class="px-5 py-2 text-center">{{ $url['can_visit'] ? '✔️' : '❌' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x:miniHelper::block>
        </div>
    </div>
</div>
