<?php

namespace App\Jobs\Translation;

use App\Jobs\RegisterProductUsage;
use App\Jobs\Traits\JobEndings;
use App\Models\Document;
use App\Models\DocumentContentBlock;
use App\Repositories\GenRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranslateTextBlock implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, JobEndings;

    protected Document $document;
    protected DocumentContentBlock $contentBlock;
    protected array $meta;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Document $document, array $meta = [])
    {
        $this->document = $document;
        $this->contentBlock = DocumentContentBlock::findOrFail($meta['content_block_id']);
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
            $response = GenRepository::translateText(
                $this->contentBlock->content,
                $this->meta['target_language']
            );
            $this->contentBlock->update([
                'content' => $response['content']
            ]);

            RegisterProductUsage::dispatch($this->contentBlock->document->account, [
                ...$response['token_usage'],
                'meta' => ['document_id' => $this->contentBlock->document->id]
            ]);
            $this->jobSucceded();
        } catch (Exception $e) {
            $this->jobFailed('Failed to translate text block: ' . $e->getMessage());
        }
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'translating_text_block_' . $this->contentBlock->id;
    }
}
