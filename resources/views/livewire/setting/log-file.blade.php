<div>
    <div class="container mx-auto p-4">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">Laravel Logs</h2>
                <p class="text-slate-600 dark:text-slate-400">Zeige die letzten {{ count($logs) }} Log-Einträge</p>
            </div>
            @if(count($logs) > 0)
                <button 
                    wire:click="deleteAllLogs"
                    wire:confirm="Möchten Sie wirklich alle Log-Einträge löschen?"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                    Alle löschen
                </button>
            @endif
        </div>

        @if(session()->has('message'))
            <div class="mb-4 p-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <div class="space-y-2">
            @forelse($logs as $index => $log)
                <div 
                    wire:click="selectLog({{ $index }})"
                    class="border rounded-lg p-4 cursor-pointer transition-all hover:shadow-md
                        {{ $selectedLogIndex === $index ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800' }}"
                >
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs font-semibold rounded
                                @if($log['level'] === 'ERROR') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                @elseif($log['level'] === 'WARNING') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                @elseif($log['level'] === 'INFO') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300
                                @endif">
                                {{ $log['level'] }}
                            </span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $log['timestamp'] }}</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $log['environment'] }}</span>
                        </div>
                        <button 
                            wire:click.stop="deleteLog({{ $index }})"
                            wire:confirm="Möchten Sie diesen Log-Eintrag wirklich löschen?"
                            class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
                            title="Log-Eintrag löschen"
                        >
                            Löschen
                        </button>
                    </div>
                    
                    <div class="text-sm text-slate-700 dark:text-slate-300 mb-2">
                        {{ $log['message'] }}
                    </div>

                    @if($selectedLogIndex === $index)
                        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-600">
                            <pre class="text-xs bg-slate-100 dark:bg-slate-900 text-slate-800 dark:text-slate-300 p-3 rounded overflow-x-auto whitespace-pre-wrap">{{ $log['full_content'] }}</pre>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    Keine Log-Einträge gefunden.
                </div>
            @endforelse
        </div>
    </div>
</div>

