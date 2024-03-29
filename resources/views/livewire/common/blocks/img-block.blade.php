<div class="h-96 mb-8">
    <div class="relative h-full group">
        <img class="rounded-xl w-full h-full object-cover" src={{ $mediaFile->file_url ??
        '/images/placeholder-social-media.jpg'
        }} />
        <div class="hidden group-hover:flex absolute top-0 left-0 h-full w-full items-center justify-center">
            <div class="z-20 flex gap-2">
                <button wire:click="$toggle('showImageGenerator')"
                    class="relative group/button transition duration-200 text-white hover:bg-secondary border border-gray-400 bg-gray-500 p-3 rounded-lg flex items-center gap-2">
                    <x-icon solid name="photograph" class="w-5 h-5" />
                    <div
                        class="absolute top-10 mt-4 w-[150px] left-1/2 transform -translate-x-1/2 mt-2 opacity-0 group-hover/button:opacity-100 transition-opacity duration-200 ease-in-out tooltip">
                        {{ __('images.regenerate_image') }}
                    </div>
                </button>
                <button wire:click="downloadImage"
                    class="relative group/button transition duration-200 text-white hover:bg-secondary border border-gray-400 bg-gray-500 p-3 rounded-lg flex items-center gap-2">
                    <x-icon solid name="arrow-circle-down" class="w-5 h-5" />
                    <div
                        class="absolute top-10 mt-4 w-[150px] left-1/2 transform -translate-x-1/2 mt-2 opacity-0 group-hover/button:opacity-100 transition-opacity duration-200 ease-in-out tooltip">
                        {{ __('images.download') }}
                    </div>
                </button>
                <button wire:click="previewImage"
                    class="relative group/button transition-bg delay-100 duration-200 text-white hover:bg-secondary hover:border-transparent border border-gray-400 bg-gray-500 p-3 rounded-lg flex items-center gap-2">
                    <x-icon name="eye" class="w-5 h-5" />
                    <div
                        class="absolute top-10 mt-4 w-[150px] left-1/2 transform -translate-x-1/2 mt-2 opacity-0 group-hover/button:opacity-100 transition-opacity duration-200 ease-in-out tooltip">
                        {{ __('images.preview') }}
                    </div>
                </button>
            </div>
        </div>
        <div
            class="group-hover:opacity-60 absolute flex items-center justify-center inset-0 bg-black rounded-t-xl opacity-0 transition-opacity duration-300 ease-in-out">
        </div>
    </div>
    @if ($showImageGenerator)
    @livewire('image.image-block-generator-modal', ['contentBlock' => $contentBlock])
    @endif
</div>