<div>
    <x-livewire-tables::tools.filter-label :$filter :$filterLayout :$tableName :$isTailwind :$isBootstrap4
        :$isBootstrap5 :$isBootstrap />
    @if ($theme === 'tailwind')
    <div class="rounded-md">
        <div>
            <input type="checkbox"
                id="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-select-all"
                wire:input="selectAllFilterOptions('{{ $filter->getKey() }}')" {{
                count($component->getAppliedFilterWithValue($filter->getKey()) ?? []) === count($filter->getOptions()) ?
            'checked' : ''}}

            class="text-secondary rounded border-gray-300 shadow-sm transition duration-150 ease-in-out
            focus:border-zinc-300 focus:ring focus:ring-zinc-200 focus:ring-opacity-50 dark:bg-gray-900 dark:text-white
            dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:bg-gray-600 disabled:opacity-50 disabled:cursor-wait"
            >
            <label
                for="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-select-all"
                class="dark:text-white">@lang('All')</label>
        </div>

        @foreach($filter->getOptions() as $key => $value)
        <div
            wire:key="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-multiselect-{{ $key }}">
            <input type="checkbox"
                id="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-{{ $loop->index }}"
                value="{{ $key }}"
                wire:key="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-{{ $loop->index }}"
                wire:model.live.stop="{{ $tableName }}.filters.{{ $filter->getKey() }}" {{
                count($component->getAppliedFilterWithValue($filter->getKey()) ?? []) === count($filter->getOptions()) ?
            'disabled' : ''}}
            :class="{'disabled:bg-gray-400 disabled:hover:bg-gray-400' : {{
            count($component->getAppliedFilterWithValue($filter->getKey()) ?? []) === count($filter->getOptions()) ?
            'true' : 'false' }}}"
            class="text-secondary rounded border-gray-300 shadow-sm transition duration-150 ease-in-out
            focus:border-indigo-300 focus:ring focus:ring-zinc-200 focus:ring-opacity-50 dark:bg-gray-900
            dark:text-white dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:bg-gray-600 disabled:opacity-50
            disabled:cursor-wait"
            >
            <label
                for="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-{{ $loop->index }}"
                class="dark:text-white">{{ $value }}</label>
        </div>
        @endforeach
    </div>
    @elseif ($theme === 'bootstrap-4' || $theme === 'bootstrap-5')
    <div class="form-check">
        <input type="checkbox"
            id="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-select-all"
            wire:input="selectAllFilterOptions('{{ $filter->getKey() }}')" {{
            count($component->getAppliedFilterWithValue($filter->getKey()) ?? []) === count($filter->getOptions()) ?
        'checked' : ''}}
        class="form-check-input"
        >
        <label class="form-check-label"
            for="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-select-all">@lang('All')</label>
    </div>

    @foreach($filter->getOptions() as $key => $value)
    <div class="form-check"
        wire:key="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-multiselect-{{ $key }}">
        <input class="form-check-input" type="checkbox"
            id="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-{{ $loop->index }}"
            value="{{ $key }}"
            wire:key="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-{{ $loop->index }}"
            wire:model.live.stop="{{ $tableName }}.filters.{{ $filter->getKey() }}">
        <label class="form-check-label"
            for="{{ $tableName }}-filter-{{ $filter->getKey() }}@if($filter->hasCustomPosition())-{{ $filter->getCustomPosition() }}@endif-{{ $loop->index }}">{{
            $value }}</label>
    </div>
    @endforeach
    @endif
</div>
