<div class="w-full flex flex-col">
    @section('header')
    <div class="flex flex-col md:flex-row items-center justify-between gap-2 md:gap-8">
        <div class="flex items-center gap-2">
            @include('livewire.common.header', ['icon' => 'search-circle', 'title' =>
            __('insight-hub.insight_hub')])
            <button onclick="livewire.dispatch('invokeNew')"
                class="flex items-center gap-2 bg-secondary text-white px-4 py-1 rounded-lg">
                <span class="font-bold text-lg">{{__('insight-hub.new')}}</span>
            </button>
        </div>
        <div class="w-full md:w-1/2">
            @include('livewire.common.page-info', ['content' => __('insight-hub.page_info')])
        </div>
    </div>
    @endsection
    @livewire('my-documents-table', ['documentTypes' => [\App\Enums\DocumentType::INQUIRY]])
</div>