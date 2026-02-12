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
                    <button type="button" wire:click="createModelFile"
                        class="px-5 py-2 text-white bg-teal-500 rounded-md hover:bg-teal-600 text-nowrap m-[2px]">Create new
                        Model</button>
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
                        @disabled(! $hasDeletedAt)
                        class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                    <span class="text-slate-700 dark:text-slate-200">
                        SoftDeletes (benötigt <code>deleted_at</code>)
                    </span>
                </label>
            </div>

            @if (! $hasDeletedAt)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Hinweis: SoftDeletes wird nur aktiv, wenn die Tabelle eine <code>deleted_at</code>-Spalte besitzt.
                </p>
            @endif
        @endif
        

        <div class="grid grid-cols-1 gap-5 mt-14">

            @if (isset($fields) && count($fields) >= 1)
                <div>
                    <h2 class="mb-3 font-bold">Protected $fillable</h2>
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
                    <h2 class="mb-3 font-bold text-slate-700 dark:text-slate-300">Protected $casts</h2>
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
