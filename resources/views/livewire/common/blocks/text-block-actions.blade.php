<div
    class="invisible opacity-0 group-hover/block:opacity-100 group-hover/block:visible transition-display duration-200 flex flex-col md:flex-row items-start md:items-center flex-wrap justify-between bg-gray-200 border border-gray-200 p-2 w-full text-sm gap-1 right-0 top-full absolute rounded-lg border-t z-50">
    <div class="flex items-center gap-1">
        @if($hasPastVersions)
        <button wire:click="undo"
            class="relative group/button flex items-center text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white border border-gray-300 px-3 py-1 rounded-lg transition ease-in-out duration-200">
            <x-icon name="rewind" width="18" height="18" />
            <div
                class="invisible absolute group-hover/button:visible top-full z-10 p-2 mt-2 text-sm font-medium leading-none text-white bg-black rounded shadow-md whitespace-nowrap">
                {{__('menus.undo')}}
            </div>
        </button>
        @endif
        @if($hasFutureVersions)
        <button wire:click="redo"
            class="relative group/button flex items-center text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white border border-gray-300 px-3 py-1 rounded-lg transition ease-in-out duration-200">
            <x-icon name="fast-forward" width="18" height="18" />
            <div
                class="invisible absolute group-hover/button:visible top-full z-10 p-2 mt-2 text-sm font-medium leading-none text-white bg-black rounded shadow-md whitespace-nowrap">
                {{__('menus.redo')}}
            </div>
        </button>
        @endif
        @if(!in_array('copy', $hide))
        <button wire:click="copy"
            class="relative group/button flex items-center text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white border border-gray-300 px-3 py-1 rounded-lg transition ease-in-out duration-200">
            <x-icon name="clipboard-copy" width="18" height="18" />
            <div
                class="invisible absolute group-hover/button:visible top-full z-10 p-2 mt-2 text-sm font-medium leading-none text-white bg-black rounded shadow-md whitespace-nowrap">
                {{__('menus.copy')}}
            </div>
        </button>
        @endif
        @if(!in_array('delete', $hide))
        <button wire:click="delete"
            class="relative group/button flex items-center text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white border border-gray-300 px-3 py-1 rounded-lg transition ease-in-out duration-200">
            <x-icon name="trash" width="18" height="18" />
            <div
                class="invisible absolute group-hover/button:visible top-full z-10 p-2 mt-2 text-sm font-medium leading-none text-white bg-black rounded shadow-md whitespace-nowrap">
                {{__('menus.delete')}}
            </div>
        </button>
        @endif
        <div
            class="relative group/button flex items-center text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white border border-gray-300 px-3 py-1 rounded-lg transition ease-in-out duration-200">
            <x-icon name="information-circle" width="18" height="18" />
            <div
                class="flex flex-col gap-2 invisible absolute group-hover/button:visible top-full z-10 p-2 mt-2 text-sm font-medium leading-none text-white bg-black rounded shadow-md whitespace-nowrap">
                <div>{{__('menus.word_count')}}: {{$info['word_count']}}</div>
                <div>{{__('menus.char_count')}}: {{$info['char_count']}}</div>
            </div>
        </div>
    </div>
    <div class="grid grid-cols-2 text-lg md:text-base w-full items-center gap-1 mt-2">
        <button
            class="flex items-center gap-1 text-gray-600 hover:bg-secondary hover:text-white hover:border-white hover:font-bold bg-white border border-gray-300 px-3 py-2 md:py-1 rounded-lg transition-all ease-in-out duration-200"
            wire:click="toggleCustomPrompt">
            <x-icon name="speakerphone" width="18" height="18" />
            <div>{{__('menus.ask_to')}}</div>
        </button>
        <button wire:click="shorten"
            class="flex items-center gap-2 text-gray-600 hover:bg-secondary hover:text-white hover:font-bold hover:border-white bg-white border border-gray-300 px-3 py-2 md:py-1 rounded-lg transition-all ease-in-out duration-200">
            <x-icon name="menu-alt-4" width="18" height="18" />
            <div>{{__('menus.shorten')}}</div>
        </button>
        <button wire:click="expand"
            class="flex items-center gap-1 text-gray-600 hover:bg-secondary hover:text-white hover:font-bold hover:border-white bg-white border border-gray-300 px-3 py-2 md:py-1 rounded-lg transition-all ease-in-out duration-200">
            <x-icon name="menu" width="18" height="18" />
            <div>{{__('menus.expand')}}</div>
        </button>
        <div
            class="relative group/more flex items-center gap-1 text-gray-600 hover:bg-secondary hover:text-white hover:border-white hover:rounded-br-none hover:rounded-bl-none rounded-lg bg-white border border-gray-300 px-3 py-2 md:py-1 transition-all ease-in-out duration-200">
            <x-icon name="sparkles" width="18" height="18" />
            <div>{{__('menus.more')}}</div>
            <div
                class="invisible group-hover/more:visible absolute flex flex-col right-0 bg-white top-full w-[200px] overflow-hidden rounded-tl-lg rounded-b-lg border border-gray-300">
                <button wire:click="paraphrase"
                    class="flex items-center hover:font-bold gap-2 text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white px-3 py-2 transition ease-in-out duration-200">
                    <x-icon name="switch-horizontal" width="18" height="18" />
                    <div>{{__('menus.paraphrase')}}</div>
                </button>
                <button wire:click="lessComplex"
                    class="flex items-center hover:font-bold gap-2 text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white px-3 py-2 transition ease-in-out duration-200">
                    <x-icon name="chevron-double-down" width="18" height="18" />
                    <div>{{__('menus.reduce_complexity')}}</div>
                </button>
                <button wire:click="moreComplex"
                    class="flex items-center hover:font-bold gap-2 text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white px-3 py-2 transition ease-in-out duration-200">
                    <x-icon name="chevron-double-up" width="18" height="18" />
                    <div>{{__('menus.increase_complexity')}}</div>
                </button>
                {{-- <button wire:click="generateAudio"
                    class="flex items-center hover:font-bold gap-2 text-gray-600 hover:bg-secondary hover:text-white hover:border-white bg-white px-3 py-1 transition ease-in-out duration-200">
                    <x-icon name="volume-up" width="18" height="18" />
                    <div>Generate AI Audio</div>
                </button> --}}
            </div>
        </div>
    </div>
</div>
