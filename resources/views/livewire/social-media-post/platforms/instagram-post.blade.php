<div class="flex flex-col h-full p-6 bg-[#B92A70] rounded-b-xl">
    <div class="flex-1">
        <div class="h-[200px]">
            <img class="rounded-t-xl w-full h-full object-cover"
                src={{ $image ?? '/images/placeholder-social-media.jpg' }} />
        </div>
        @livewire('common.blocks.text-block', ['content' => $text, 'contentBlockId' => $textBlockId, 'faster' => true])
    </div>

    {{-- @if ($displayHistory)
        @livewire('common.history-modal', [$document])
    @endif --}}
</div>
