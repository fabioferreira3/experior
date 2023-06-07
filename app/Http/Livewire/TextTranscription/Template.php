<?php

namespace App\Http\Livewire\TextTranscription;

use Livewire\Component;

class Template extends Component
{
    public string $icon;
    public string $title;
    public string $description;

    public function __construct()
    {
        $this->icon = 'chat-alt';
        $this->title = __('templates.text_transcription');
        $this->description = __('templates.transcribe_audio');
    }

    public function render()
    {
        return view('livewire.common.template');
    }

    public function execute()
    {
        return redirect()->to('/transcription/new');
    }
}
