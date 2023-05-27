<div class="flex flex-col gap-2 p-4 bg-zinc-200 rounded-lg border border-gray-300">
    <div class="flex justify-between">
        <label for="title" class="font-bold text-xl">Title</label>
        <div class="flex gap-2">
            @include('livewire.common.field-actions')
        </div>
    </div>
    <x-input placeholder="Post title" class="mt-2 p-3 rounded-lg border border-zinc-200" wire:model="content" type="text" name="title" />
</div>