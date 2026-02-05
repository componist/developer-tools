<div>
    <div class="p-7">
        <div class="flex flex-wrap gap-7">

            @foreach ($content as $value)
                <div class="min-w-96">
                    <div class="grid grid-cols-1 p-3 bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-600 rounded-lg shadow-sm"
                        style="border-top: 10px;
    border-style: inset; border-color: #{{ substr(str_shuffle('ABCDEF0123456789'), 0, 6) }};">
                        <div>
                            <span class="block py-3 font-bold text-blue-500 dark:text-blue-400">{{ $value['table'] }}</span>
                        </div>
                        <table>
                            <tbody class="divide-y divide-gray-200 dark:divide-slate-600">
                                @foreach ($value['fields'] as $field)
                                    <tr>
                                        <td class="py-2 text-slate-600 dark:text-slate-400">{{ $field['Field'] }}</td>
                                        <td class="py-2 text-slate-400 dark:text-slate-500">{{ $field['Type'] }}</td>
                                        <td class="py-2 text-slate-600 dark:text-slate-400">
                                            @if ($field['Null'] === 'YES')
                                                <span class="font-bold ">N</span>
                                            @else
                                                <span class="font-bold text-red-500">N</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
