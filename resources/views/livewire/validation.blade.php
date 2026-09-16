<div>
    <div class="container mx-auto">

        @if ($message)
            <div
                class="mb-4 rounded-md p-4 {{ $messageSuccess ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-800 dark:text-teal-200' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' }}">
                {{ $message }}
            </div>
        @endif

        <div class="flex items-end justify-between gap-5">
            <div class="w-full relative">
                <x:developerTools::form.label for="validation-table-select">Table</x:developerTools::form.label>
                <x:developerTools::form.select id="validation-table-select" size="1"
                    wire:model.live.debounce.300ms="table">
                    <option value=""></option>
                    @foreach ($table_list as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </x:developerTools::form.select>
                <div wire:loading wire:target="table"
                    class="absolute right-3 top-9 text-sm text-slate-500">
                    …
                </div>
            </div>

            <div class="flex items-end">
                @if ($table && $generateFormRequestClass)
                    <x:component::button.primary wire:click="createFormRequestFile">
                        Form Request erstellen ({{ $this->formRequestClassName }})
                    </x:component::button.primary>
                @endif
            </div>
        </div>

        @if ($table)
            <div class="mt-4 flex flex-wrap gap-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.live="generateFormRequestClass"
                        class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-slate-700 dark:text-slate-200">
                        Form Request-Klasse erzeugen
                        <span class="text-xs text-slate-500 dark:text-slate-400">(unter
                            <code>App\Http\Requests</code>)</span>
                    </span>
                </label>
            </div>
        @endif

        <div class="mt-14 grid grid-cols-1 gap-8">
            @if (count($validator) > 0)
                @php
                    $validationLines = collect($validator)->map(fn ($rules, $field) => "    '".addslashes($field)."' => '".addslashes($rules)."'")->join(",\n");
                    $validationSnippet = "\$validated = \$request->validate([\n".$validationLines."\n]);";
                @endphp
                {{-- Controller: $request->validate() --}}
                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm dark:border-slate-600 dark:bg-slate-800/80"
                    x-data="{ copied: false }"
                >
                    <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-slate-100 px-4 py-3 dark:border-slate-600 dark:bg-slate-700/50">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                            <h2 class="font-semibold text-slate-700 dark:text-slate-200">Im Controller</h2>
                        </div>
                        <button
                            type="button"
                            @click="
                                navigator.clipboard.writeText($refs.snippetController.innerText);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            "
                            class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-600 dark:hover:text-slate-100"
                        >
                            <span x-show="!copied">Kopieren</span>
                            <span x-show="copied" x-cloak x-transition class="text-emerald-600 dark:text-emerald-400">Kopiert</span>
                        </button>
                    </div>
                    <pre
                        x-ref="snippetController"
                        class="overflow-x-auto p-4 text-sm leading-relaxed text-slate-800 dark:text-slate-200"
                        style="font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, monospace;"
                    ><code>{{ $validationSnippet }}</code></pre>
                </div>

                {{-- Form Request (Laravel Stub) --}}
                <div
                    class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 shadow-sm dark:border-slate-600 dark:bg-slate-800/80"
                    x-data="{ copied: false }"
                >
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-100 px-4 py-3 dark:border-slate-600 dark:bg-slate-700/50">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-2 w-2 rounded-full bg-teal-500" aria-hidden="true"></span>
                            <h2 class="font-semibold text-slate-700 dark:text-slate-200">Form Request</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 dark:text-slate-400">z. B. <code class="rounded bg-slate-200 px-1 dark:bg-slate-600">php artisan make:request {{ $this->formRequestClassName }}</code></span>
                            <button
                                type="button"
                                @click="
                                    navigator.clipboard.writeText($refs.snippetFormRequest.innerText);
                                    copied = true;
                                    setTimeout(() => copied = false, 2000);
                                "
                                class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-600 dark:hover:text-slate-100"
                            >
                                <span x-show="!copied">Kopieren</span>
                                <span x-show="copied" x-cloak x-transition class="text-emerald-600 dark:text-emerald-400">Kopiert</span>
                            </button>
                        </div>
                    </div>
                    <pre
                        x-ref="snippetFormRequest"
                        class="overflow-x-auto p-4 text-sm leading-relaxed text-slate-800 dark:text-slate-200"
                        style="font-family: ui-monospace, 'Cascadia Code', 'Source Code Pro', Menlo, Consolas, monospace;"
                    ><code>{!! $this->formRequestStub !!}</code></pre>
                </div>
            @endif
        </div>
    </div>
</div>
