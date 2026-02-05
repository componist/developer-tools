<div>
    <div x-data="{ showMsg: false }" class="container mx-auto">

        <div x-cloak x-show="showMsg" @click.away="showMsg = false"
            class="fixed z-50 max-w-sm overflow-hidden bg-teal-100 dark:bg-teal-900/40 border border-teal-300 dark:border-teal-700 rounded-md top-3 right-3">
            <p class="flex items-center justify-center p-3 text-teal-600 dark:text-teal-300">Copied to Clipboard</p>
        </div>


        @foreach ($content as $key => $group)
            @if ($key == 'componist')
                <div class="flex items-center gap-1 mb-2 ml-2 mt-14">
                    <div class="flex items-center justify-center w-10 h-10 text-yellow-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                            </polygon>
                        </svg>
                    </div>
                    <p class="text-2xl font-bold text-teal-500 uppercase ">{{ $key }}</p>
                </div>
            @else
                <p class="mb-2 ml-2 text-2xl font-bold uppercase text-slate-500 dark:text-slate-400 mt-14">{{ $key }}</p>
            @endif

            <x:miniHelper::block>
                @foreach ($group as $artisan)
                    <x:miniHelper::item>
                        <button type="button"
                            class="block px-5 py-1 text-white bg-teal-500 rounded-full shadow-sm hover:bg-teal-600"
                            @click.prevent="navigator.clipboard.writeText('{{ $artisan['name'] }}'), showMsg = true, setTimeout(() => showMsg = false, 1000)">{{ $artisan['name'] }}</button>

                        <p class="text-slate-600 dark:text-slate-400">{{ $artisan['description'] }}</p>
                    </x:miniHelper::item>
                @endforeach
            </x:miniHelper::block>
        @endforeach
    </div>
</div>
