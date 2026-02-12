<div>
    <div class="container mx-auto">
        <div class="mb-14">
            <x:developerTools::form.label>Table</x:developerTools::form.label>
            <x:developerTools::form.select size="1" wire:model.live.debounce.500ms="table"
                wire:change='getTable($event.target.value)' class="px-5 py-2 bg-slate-100 dark:bg-slate-800 rounded-md">
                <option value=""></option>
                @foreach ($table_list as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </x:developerTools::form.select>
        </div>

        <div>

            @if ($fields)
                <div class="p-5 text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-lg">
                    <code>
                        @foreach ($fields as $field)
                            {{ $field['html'] }}<br />
                        @endforeach
                    </code>

                </div>
            @endif

        </div>

    </div>
</div>
