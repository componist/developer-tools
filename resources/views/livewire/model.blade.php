<div>
    <div class="container mx-auto">
        @if ($message)
            <div class="p-4 mb-4 rounded-md {{ $messageSuccess ? 'bg-teal-100 dark:bg-teal-900/30 text-teal-800 dark:text-teal-200' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200' }}">
                {{ $message }}
            </div>
        @endif
        <div class="flex items-end justify-between gap-5">
            <div class="w-full">
                <x:developerTools::form.label>Table</x:developerTools::form.label>
                <x:developerTools::form.select size="1" wire:model.live.debounce.300ms="table">
                    <option value=""></option>
                    @foreach ($table_list as $value)
                        <option value="{{ $value }}">{{ $value }}</option>
                    @endforeach
                </x:developerTools::form.select>
    
            </div>
    
            <div class="flex items-end">
                @if ($table && $model && $model['existsModel'] === false)
                    <x:component::button.primary wire:click="createModelFile">
                        Model erstellen
                    </x:component::button.primary>
                @endif
            </div>
        </div>

        @if ($table)
            <div class="flex flex-wrap gap-4 mt-4 text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.live="useHasFactory"
                        class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-slate-700 dark:text-slate-200">HasFactory Trait verwenden</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.live="generateCasts"
                        class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-slate-700 dark:text-slate-200">$casts generieren</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.live="enableSoftDeletes"
                        class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-slate-700 dark:text-slate-200">SoftDeletes verwenden</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" wire:model.live="generateFactory"
                        class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-slate-700 dark:text-slate-200">Factory generieren</span>
                </label>
            </div>

            @if ($enableSoftDeletes && ! $hasDeletedAt)
                <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                    Hinweis: Tabelle hat keine <code>deleted_at</code>-Spalte. Spalte wird angelegt.
                </p>
            @endif
        @endif

        @if (isset($fields) && count($fields) >= 1 && $generateFactory)
            <div class="mt-8">
                <h2 class="mb-3 text-lg font-bold text-slate-800 dark:text-slate-100">Factory-Spalten konfigurieren</h2>
                <p class="mb-3 text-sm text-slate-600 dark:text-slate-400">
                    Pro Spalte den Faker-Typ wählen. Pflichtfelder (Null: NO) werden angezeigt; bei nullable Spalten kann optional <code>null</code> gesetzt werden.
                </p>
                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-600">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-600">
                        <thead class="bg-slate-50 dark:bg-slate-800">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-300 uppercase">Spalte</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-300 uppercase">Typ</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-300 uppercase">Pflicht</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600 dark:text-slate-300 uppercase">Faker-Typ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-600 bg-white dark:bg-slate-900">
                            @foreach ($fields as $index => $field)
                                @php
                                    $colName = $field['Field'] ?? '';
                                    $skip = in_array($colName, ['id', 'created_at', 'updated_at', 'deleted_at'], true);
                                @endphp
                                @if (! $skip)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="px-4 py-2 text-sm font-mono text-slate-800 dark:text-slate-200">{{ $colName }}</td>
                                        <td class="px-4 py-2 text-sm text-slate-600 dark:text-slate-400">{{ $field['Type'] ?? '' }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            @if (($field['Null'] ?? '') === 'YES')
                                                <span class="text-amber-600 dark:text-amber-400">nullable</span>
                                            @else
                                                <span class="text-slate-700 dark:text-slate-300">ja</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <x:developerTools::form.select wire:model.live="fields.{{ $index }}.fake_type"
                                                class="max-w-xs text-sm">
                                                @foreach ($this->getAvailableFakerTypes() as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </x:developerTools::form.select>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-5 mt-14">

            @if (isset($fields) && count($fields) >= 1)
                <div>
                    <h2 class="mb-3 text-lg font-bold text-slate-800 dark:text-slate-100">Protected $fillable</h2>
                    <x:developerTools::block>
                        <pre data-enlighter-language="php">protected $fillable = [
@php
    foreach ($fields as $field) {
        if ($field['Field'] != 'id') {
            echo "\t'" . $field['Field'] . "',\n";
        }
    }
@endphp];</pre>
                    </x:developerTools::block>
                </div>

                <div>
                    <h2 class="mb-3 text-lg font-bold text-slate-800 dark:text-slate-100">Protected $casts</h2>
                    <x:developerTools::block>
                        <pre data-enlighter-language="php">/**
* The attributes that should be cast to native types.
*
* @var array &lt;string, string&gt;
*/
protected $casts = [
@php
    foreach ($fields as $field) {
        $string = "\t";
        $string .= '\'' . $field['Field'] . '\' => \'';

        if (strstr($field['Type'], 'bigint') or strstr($field['Type'], 'int')) {
            $string .= 'integer';
        }

        if (strstr($field['Type'], 'varchar')) {
            $string .= 'string';
        }

        if (strstr($field['Type'], 'text') or strstr($field['Type'], 'longtext')) {
            $string .= 'string';
        }

        if (strstr($field['Type'], 'double')) {
            $string .= 'double';
        }

        if (strstr($field['Type'], 'timestamp') or strstr($field['Type'], 'date')) {
            $string .= 'datetime';
        }

        if (strstr($field['Type'], 'float')) {
            $string .= 'float';
        }

        $string .= '\',';

        echo $string . "\n";
    }
@endphp];</pre>
                    </x:developerTools::block>
                </div>
            @endif
        </div>




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
