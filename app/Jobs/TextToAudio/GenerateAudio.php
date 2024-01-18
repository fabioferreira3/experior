<?php

namespace App\Jobs\TextToAudio;

use App\Enums\AIModel;
use App\Enums\MediaType;
use App\Events\AudioGenerated;
use App\Jobs\RegisterProductUsage;
use App\Jobs\Traits\JobEndings;
use App\Models\Document;
use App\Models\MediaFile;
use App\Models\User;
use App\Models\Voice;
use App\Repositories\DocumentRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Talendor\ElevenLabsClient\TextToSpeech\TextToSpeech;

class GenerateAudio implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, JobEndings;

    protected Document $document;
    protected DocumentRepository $repo;
    protected array $meta;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Document $document, array $meta = [])
    {
        $this->document = $document->fresh();
        $this->meta = $meta;
        $this->repo = new DocumentRepository($this->document);
        $this->onQueue('voice_generation');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $user = User::findOrFail($this->document->meta['user_id']);
            $voice = Voice::findOrFail($this->meta['voice_id']);
            $client = app(TextToSpeech::class);
            $response = $client->generate($this->meta['input_text'], $voice->external_id, 0, 'eleven_multilingual_v2');
            $audioContent = $response['response_body'];

            $filePath = 'ai-audio/' . Str::uuid() . '.mp3';
            Storage::disk('s3')->put($filePath, $audioContent);

            $mediaFile = MediaFile::create([
                'account_id' => $user->account_id,
                'file_path' => $filePath,
                'type' => MediaType::AUDIO,
                'meta' => [
                    'document_id' => $this->document->id
                ]
            ]);

            RegisterProductUsage::dispatch($this->document->account, [
                'model' => AIModel::ELEVEN_LABS->value,
                'meta' => ['document_id' => $this->document->id]
            ]);

            AudioGenerated::dispatchIf(
                $this->document->meta['user_id'],
                [
                    'user_id' => $this->document->meta['user_id'],
                    'media_file_id' => $mediaFile->id,
                    'process_id' => $this->meta['process_id']
                ]
            );

            $this->jobSucceded(true);
        } catch (Exception $e) {
            $this->handleError($e, 'Failed to generating audio');
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'generating_audio_' . $this->meta['process_id'] ?? $this->document->id;
    }
}
