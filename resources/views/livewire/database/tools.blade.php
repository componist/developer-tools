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

        <div class="flex items-center gap-3 mt-7">
            <button type="button" @click.prevent="$wire.clearTables"
                class="px-5 py-1 text-white bg-teal-500 rounded-md hover:bg-teal-600">clear</button>
            <button type="button" @click.prevent="$wire.deleteTables"
                class="px-5 py-1 text-white bg-red-500 rounded-md hover:bg-red-600">delete</button>
        </div>

    </div>
</div>
