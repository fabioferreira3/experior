<div class="flex flex-col gap-2 p-4 bg-zinc-200 rounded-lg border border-gray-300">
    @section('header')
    @include('livewire.common.field-actions', [
    'copyAction' => true,
    'regenerateAction' => true,
    'historyAction' => true,
    ])
    @endsection
    <textarea class="text-lg mt-2 rounded-lg border border-zinc-200" name="content" wire:model.live="content"
        rows="{{ $rows }}"></textarea>
</div>
