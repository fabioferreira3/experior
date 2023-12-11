<div class="fixed sticky top-0 flex items-center justify-between bg-white">
    <div class="w-full">
        <div class="flex items-center gap-4 text-zinc-700">
            <x-icon name="{{$icon}}" class="w-14 h-14 text-secondary" />
            <div class="flex flex-col w-full">
                @if($editable ?? false) <input class="text-2xl md:text-4xl font-bold p-0 border-0
                        hover:border hover:border-gray-300 rounded-lg place-self-start" wire:model.lazy="title" />
                @else
                <h1 class="text-2xl md:text-4xl font-bold">
                    {{$title}}
                </h1>@endif
                @isset($suffix)<h2 class="text-xl md:text-2xl font-bold">{{$suffix ?? ''}}</h2>@endisset
            </div>
        </div>
    </div>
</div>