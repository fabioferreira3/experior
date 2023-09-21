<div class="flex flex-col h-full p-6 bg-[#0078F6] rounded-b-xl">
    {{-- @include('livewire.common.field-actions', ['copyAction' => true, 'regenerateAction' => true, 'historyAction' => true]) --}}
    <div class="flex-1">
        <div class="h-[200px]">
            <img class="rounded-t-xl w-full h-full object-cover"
                src={{ $image ?? '/images/placeholder-social-media.jpg' }} />
        </div>
        @livewire('common.blocks.text-block', ['content' => $text, 'contentBlockId' => $textBlockId])
    </div>

    @if ($displayHistory)
        @livewire('common.history-modal', [$document])
    @endif
</div>
