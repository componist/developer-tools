<div>
    <div class="container mx-auto">

        <x:developerTools::block>
            <table class="w-full border border-slate-300 dark:border-slate-600 border-collapse">
                <tr class="sticky top-0 text-sm font-bold text-white uppercase bg-slate-600 dark:bg-slate-700">
                    <td class="px-3 py-2 border border-slate-300 dark:border-slate-600 border-collapse text-nowrap"></td>
                    <td class="px-3 py-2 border border-slate-300 dark:border-slate-600 border-collapse text-nowrap">Table Name</td>
                </tr>
                <tbody class="text-slate-500 dark:text-slate-400">
                    @foreach ($content as $key => $value)
                        <tr class="hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-slate-900 dark:hover:text-slate-100">
                            <td class="px-3 py-2 font-bold text-center border border-slate-300 dark:border-slate-600 border-collapse">
                                <input type="checkbox" class="w-5 h-5 rounded-md"
                                    wire:model.live="content.{{ $key }}.checked" />
                            </td>
                            <td class="px-3 py-2 font-bold border border-slate-300 dark:border-slate-600 border-collapse">
                                {{ $value['name'] }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x:developerTools::block>

        <div class="mt-7 flex items-center gap-3">
            <x:component::button.primary @click.prevent="$wire.clearTables">
                Leeren
            </x:component::button.primary>
            <x:component::button.primary class="!bg-red-500 hover:!bg-red-600 focus:!ring-red-500"
                @click.prevent="$wire.deleteTables">
                Löschen
            </x:component::button.primary>
        </div>

    </div>
</div>
