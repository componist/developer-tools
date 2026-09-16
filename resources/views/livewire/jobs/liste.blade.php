<div>
    <div class="container mx-auto">
        <x:developerTools::block class="">
            <div class="grid grid-cols-1 gap-5">
                @foreach ($content as $job)
                    <div class="p-5 rounded-md bg-slate-100 dark:bg-slate-700 gap-7 text-slate-700 dark:text-slate-300">
                        <div class="flex items-center justify-between gap-5">
                            <div>
                                <p>Namespace: <b>{{ $job['namespace'] }}</b></p>
                                <p>Class: <b>{{ $job['class'] }}</b></p>
                            </div>
                            <div>
                                <x:component::button.primary wire:click="runJob({{ json_encode($job) }})">
                                    Job starten
                                </x:component::button.primary>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x:developerTools::block>
    </div>
</div>
