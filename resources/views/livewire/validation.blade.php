<div>
    <div class="container mx-auto">

        <div>
            <x:miniHelper::form.label>Table</x:miniHelper::form.label>
            <x:miniHelper::form.select size="1" wire:model.live.debounce.500ms="table"
                wire:change='getTable($event.target.value)' class="px-5 py-2 bg-slate-100 dark:bg-slate-800 rounded-md">
                <option value=""></option>
                @foreach ($table_list as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </x:miniHelper::form.select>
        </div>


        <div class="grid grid-cols-1 gap-5 mt-14">

            @if (count($validator) > 0)
                <div class="relative p-5 bg-slate-100 dark:bg-slate-800 rounded-md shadow-sm">
                    <h2 class="mb-3 font-bold text-slate-700 dark:text-slate-300">Request $validated</h2>

                    <pre data-enlighter-language="php">$validated = $request->validate([
                            @foreach ($validator as $fieldName => $value)
'{{ $fieldName }}' => '{{ $value }}',
@endforeach]);
                        </pre>

                </div>
            @endif
        </div>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('morph.updated', ({
                    el,
                    component
                }) => {
                    //
                    EnlighterJS.init('pre', 'code', {
                        language: 'javascript,php, html, css',
                        theme: 'dracula',
                        indent: 2
                    });
                })
            })
        </script>

    </div>
</div>
