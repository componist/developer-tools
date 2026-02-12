<div>
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold text-teal-500">Icons Liste</h1>

        <div class="flex items-end gap-5 mt-7">

            <div class="w-36">
                <x:developerTools::form.label>Package Prefix:</x:developerTools::form.label>
                <x:developerTools::form.input type="text" wire:model.live='prefix' placeholder="core::" />
            </div>
            <div class="w-full">
                <x:developerTools::form.label>Path:</x:developerTools::form.label>
                <x:developerTools::form.input type="text" wire:model.live='path' placeholder="{{ $path }}" />
            </div>

            <button type="button" @click.prevent="$wire.scanIconPath()"
                class="inline-block px-3 py-1 text-white bg-teal-500 rounded-lg hover:bg-teal-600">
                Auslesen
            </button>
        </div>

        <div class="mt-7">

            {{-- <x-dynamic-component :component="'core::' . 'icon.analytics'" /> --}}

            @if (count($content) > 0)
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($content as $icon)
                        {{-- @if (\Illuminate\Support\Facades\View::exists($icon)) --}}
                        <div class="items-center gap-1 md:flex">
                            <div class="flex items-center justify-center p-1 w-14 h-14">
                                <x-dynamic-component :component="$icon" />
                            </div>
                            <div>
                                <span
                                    class="inline-block px-5 py-1 text-white bg-teal-500 rounded-full hover:bg-teal-600">&lt;x-{{ $icon }}/&gt;</span>
                            </div>
                        </div>
                        {{-- @endif --}}
                    @endforeach
                </div>
            @else
                <p class="italic text-red-500 dark:text-red-400">keine icons gefunden</p>
            @endif

        </div>
    </div>
</div>
