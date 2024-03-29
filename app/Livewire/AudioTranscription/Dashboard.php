<?php

namespace App\Livewire\AudioTranscription;

use App\Enums\DocumentTaskEnum;
use WireUi\Traits\Actions;
use Livewire\Component;

class Dashboard extends Component
{
    use Actions;

    protected $listeners = [
        'invokeNew' => 'new',
        'InsufficientUnitsValidated' => 'handleInsufficientUnits'
    ];

    public function handleInsufficientUnits($event)
    {
        if (
            $event->task === DocumentTaskEnum::TRANSCRIBE_AUDIO->value ||
            $event->task === DocumentTaskEnum::TRANSCRIBE_AUDIO_WITH_DIARIZATION->value
        ) {
            $this->dispatch(
                'alert',
                type: 'error',
                message: __('alerts.insufficient_units')
            );
        }
    }

    public function render()
    {
        return view('livewire.audio-transcription.dashboard')->title(__('transcription.audio_transcription'));
    }

    public function new()
    {
        return redirect()->route('new-audio-transcription');
    }
}
