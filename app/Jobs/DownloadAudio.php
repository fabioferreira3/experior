<?php

namespace App\Jobs;

use App\Models\TextRequest;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

class DownloadAudio implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public TextRequest $textRequest;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TextRequest $textRequest)
    {
        $this->textRequest = $textRequest->fresh();
    }

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 1;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->textRequest->audio_file_path) {
                return;
            }

            $yt = new YoutubeDl();
            if (app()->environment('production')) {
                $yt->setBinPath('/app/yt-dlp');
            } else {
                $yt->setBinPath('/usr/local/bin/yt-dlp');
            }

            $fileName = Str::uuid() . '.%(ext)s';

            $collection = $yt->download(
                Options::create()
                    ->downloadPath(storage_path('app'))
                    ->extractAudio(true)
                    ->audioFormat('mp3')
                    ->audioQuality('0') // best
                    ->output($fileName)
                    ->url($this->textRequest->source_url)
            )->getVideos();

            if (!$collection && !isset($collection[0])) {
                throw new Exception('Audio download error: unable to download');
            }

            $localFilePath = $collection[0]->getFile();
            Storage::disk('s3')->put($collection[0]->getFile()->getBasename(), file_get_contents($localFilePath));

            $this->textRequest->update(['audio_file_path' => $collection[0]->getFile()->getBasename()]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new Exception('Audio download error: ' . $e->getMessage());
        }
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new ThrottlesExceptions(10, 5)];
    }

    /**
     * Determine the time at which the job should timeout.
     *
     * @return \DateTime
     */
    public function retryUntil()
    {
        return now()->addMinutes(2);
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'download_audio_' . $this->textRequest->id;
    }
}
