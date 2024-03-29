<x-form-section submit="updateLanguage">
    <x-slot name="title">
        {{ __('profile.language_region') }}
    </x-slot>

    <x-slot name="description">
        {{ __('profile.update_app_language') }}
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6 sm:col-span-4">
            <x-label for="language" value="{{ __('profile.language') }}" />
            <select id="language" name="language" wire:model="state.language"
                class="w-full p-3 rounded-lg border border-zinc-200">
                @foreach(App\Enums\Language::systemEnabled() as $language)
                <option value={{$language->value}}>{{$language->label()}}</option>
                @endforeach
            </select>
            <x-input-error for="language" class="mt-2" />
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="saved">
            {{ __('profile.saved') }}
        </x-action-message>

        <x-button class="bg-secondary text-white" wire:loading.attr="disabled">
            {{ __('profile.save') }}
        </x-button>

    </x-slot>
</x-form-section>
