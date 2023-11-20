<div class="flex flex-col gap-6">
    @include('livewire.common.header', ['icon' => 'search-circle', 'title' => __('inquiry-hub.inquiry_hub')])
    <div class="flex mt-52 justify-center w-full">
        <button wire:click="createNewInquiry"
            class="flex items-center gap-2 bg-secondary text-white px-3 py-2 rounded-lg text-lg">
            <x-icon name="search-circle" color="white" width="28" height="28" />
            <span class="font-bold text-2xl">{{__('inquiry-hub.new')}}</span>
        </button>
    </div>
</div>