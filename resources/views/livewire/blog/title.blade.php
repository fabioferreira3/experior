<div class="flex flex-col gap-2 p-4 bg-primary rounded-lg border border-gray-200">
    <div class="flex justify-between">
        @include('livewire.common.label', ['title' => 'Title'])
        <div class="flex gap-2">
            @include('livewire.common.field-actions', ['copyAction' => true, 'regenerateAction' => true, 'historyAction' => true])
        </div>
    </div>
    <x-input placeholder="Post title" class="mt-2 p-3 rounded-lg border border-zinc-200" wire:model="content" type="text" name="title" />
</div>
