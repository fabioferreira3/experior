<?php

namespace App\Jobs\Images;

use App\Jobs\Traits\JobEndings;
use App\Models\Document;
use App\Repositories\DocumentRepository;
use App\Repositories\GenRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateImage implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, JobEndings;

    protected Document $document;
    protected array $meta;
    protected DocumentRepository $repo;

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
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            GenRepository::generateSocialMediaPost($this->document, $this->meta['platform']);
            $this->jobSucceded();
        } catch (Exception $e) {
            $this->jobFailed('Failed to generate ' . $this->meta['platform'] . ' post: ' . $e->getMessage());
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'create_social_media_post_' . $this->meta['platform'] . $this->meta['process_id'] ?? $this->document->id;
    }
}
