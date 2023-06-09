<div class="flex flex-col gap-6">
    @include('livewire.common.header', ['icon' => 'newspaper', 'label' => 'New blog post'])
    <div class="flex flex-col md:grid md:grid-cols-6 gap-6 md:mt-12">
        <div class="col-span-2">
            <div class="p-4 bg-zinc-200 rounded-lg">
                <h2 class="font-bold text-xl">{{__('blog.instructions')}}</h2>
                <div class="flex flex-col gap-2 mt-2">
                    {!! $this->instructions !!}
                </div>
            </div>
        </div>
        <div class="col-span-4">
            <div class="flex flex-col gap-4 p-4 border rounded">
                <div class="w-full flex flex-col md:grid md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-2 items-center">
                            <label>{{__('blog.source')}}:</label>
                            <x-icon solid name="question-mark-circle" class="text-zinc-500 cursor-pointer h-5 w-5" wire:click="setSourceInfo()" />
                        </div>
                        <select name="provider" wire:model="source" class="p-3 rounded-lg border border-zinc-200">
                            @include('livewire.common.source-providers-options')
                        </select>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-2 items-center">
                            <label>{{__('blog.keyword')}}:</label>
                            <x-icon solid name="question-mark-circle" class="text-zinc-500 cursor-pointer h-5 w-5" wire:click="setKeywordInfo()" />
                        </div>
                        <input name="keyword" wire:model="keyword" class="p-3 rounded-lg border border-zinc-200" />
                        @if($errors->has('keyword'))
                        <span class="text-red-500 text-sm">{{ $errors->first('keyword') }}</span>
                        @endif
                    </div>
                </div>
                @if ($source === 'youtube')
                <div class="flex flex-col gap-3">
                    <label>Youtube url:</label>
                    <input name="url" wire:model="source_url" class="p-3 border border-zinc-200 rounded-lg" />
                    @if($errors->has('source_url'))
                    <span class="text-red-500 text-sm">{{ $errors->first('source_url') }}</span>
                    @endif
                </div>
                @endif

                @if ($source === 'website_url')
                <div class="flex flex-col gap-3">
                    <label>URL:</label>
                    <input name="url" wire:model="source_url" class="p-3 border border-zinc-200 rounded-lg" />
                    @if($errors->has('source_url'))
                    <span class="text-red-500 text-sm">{{ $errors->first('source_url') }}</span>
                    @endif
                </div>
                @endif

                <div class="w-full flex flex-col md:grid md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-2 items-center">
                            <label>{{__('blog.topics_number')}}:</label>
                            <x-icon solid name="question-mark-circle" class="text-zinc-500 cursor-pointer h-5 w-5" wire:click="setSubtopicsInfo()" />
                        </div>
                        <input type="number" max="15" name="target_headers_count" wire:model="targetHeadersCount" class="p-3 rounded-lg border border-zinc-200" />
                        @if($errors->has('targetHeadersCount'))
                        <span class="text-red-500 text-sm">{{ $errors->first('targetHeadersCount') }}</span>
                        @endif
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-2 items-center">
                            <label>{{__('blog.language')}}:</label>
                            <x-icon solid name="question-mark-circle" class="text-zinc-500 cursor-pointer h-5 w-5" wire:click="setLanguageInfo()" />
                        </div>
                        <select name="language" wire:model="language" class="p-3 rounded-lg border border-zinc-200">
                            @include('livewire.common.languages-options')
                        </select>
                        @if($errors->has('language'))
                        <span class="text-red-500 text-sm">{{ $errors->first('language') }}</span>
                        @endif
                    </div>
                </div>
                <div class="w-full flex flex-col md:grid md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-2 items-center">
                            <label>{{__('blog.writing_style')}}:</label>
                            <x-icon solid name="question-mark-circle" class="text-zinc-500 cursor-pointer h-5 w-5" wire:click="setStyleInfo()" />
                        </div>
                        <select name="style" wire:model="style" class="p-3 rounded-lg border border-zinc-200">
                            <option value="">{{__('blog.default')}}</option>
                            @include('livewire.common.styles-options')
                        </select>
                    </div>
                    <div class="flex flex-col gap-3">
                        <div class="flex gap-2 items-center">
                            <label>{{__('blog.tone')}}:</label>
                            <x-icon solid name="question-mark-circle" class="text-zinc-500 cursor-pointer h-5 w-5" wire:click="setToneInfo()" />
                        </div>
                        <select name="tone" wire:model="tone" class="p-3 rounded-lg border border-zinc-200">
                            <option value="">{{__('blog.default')}}</option>
                            @include('livewire.common.tones-options')
                        </select>
                    </div>
                </div>

                @if ($source === 'free_text')
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col gap-1">
                        <label>{{__('blog.context')}}:</label>
                        <small>{{__('blog.briefly_describe')}}</small>
                        <small>{{__('blog.paste_content')}}</small>
                    </div>

                    <textarea class="border border-zinc-200 rounded-lg" rows="8" maxlength="30000" wire:model="context"></textarea>
                    @if($errors->has('context'))
                    <span class="text-red-500 text-sm">{{ $errors->first('context') }}</span>
                    @endif
                </div>
                @endif

                <div class="flex justify-center mt-4">
                    <button wire:click="process" wire:loading.remove class="bg-secondary text-white font-bold px-4 py-2 rounded-lg">
                        {{__('blog.generate')}}!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
