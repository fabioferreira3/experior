<?php

namespace App\Jobs;

use App\Enums\DataType;
use App\Jobs\Traits\JobEndings;
use App\Models\Document;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SummarizeDocument implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, JobEndings;

    protected Document $document;
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
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if (isset($this->document->meta['context']) && Str::wordCount($this->document->meta['context']) < 1000) {
                $this->jobSkipped();
                return;
            }

            EmbedSource::dispatch($this->document, [
                'data_type' => DataType::TEXT->value,
                'source' => $this->document->meta['context']
            ]);

            $this->jobSucceded();
        } catch (Exception $e) {
            $this->jobFailed('Failed to embed summary: ' . $e->getMessage());
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'create_summary_' . $this->meta['process_id'] ?? $this->document->id;
    }
}
