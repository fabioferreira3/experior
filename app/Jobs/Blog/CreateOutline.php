<?php

namespace App\Jobs\Blog;

use App\Helpers\DocumentHelper;
use App\Helpers\PromptHelperFactory;
use App\Jobs\RegisterProductUsage;
use App\Jobs\Traits\JobEndings;
use App\Models\Document;
use App\Models\User;
use App\Packages\OpenAI\ChatGPT;
use App\Packages\Oraculum\Oraculum;
use App\Repositories\DocumentRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateOutline implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, JobEndings;

    public Document $document;
    public array $meta;
    public $promptHelper;
    public DocumentRepository $repo;
    public $oraculum;
    public $chatGpt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Document $document, array $meta = [])
    {
        $this->document = $document->fresh();
        $this->meta = $meta;
        $this->promptHelper = PromptHelperFactory::create($document->language->value);
        $this->repo = new DocumentRepository($this->document);
        $user = User::findOrFail($this->document->getMeta('user_id'));
        $this->oraculum = new Oraculum($user, $this->meta['collection_name']);
        $this->chatGpt = new ChatGPT();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            if ($this->meta['query_embedding'] ?? false) {
                $response = $this->queryEmbedding();
            } else {
                $response = $this->queryGpt();
            }

            $this->repo->updateMeta('outline', $response['content']);
            $this->repo->updateMeta('raw_structure', DocumentHelper::parseOutlineToRawStructure($response['content']));

            RegisterProductUsage::dispatch($this->document->account, [
                ...$response['token_usage'],
                'meta' => ['document_id' => $this->document->id]
            ]);
            $this->jobSucceded();
        } catch (Exception $e) {
            $this->jobFailed('Failed to generate outline: ' . $e->getMessage());
        }
    }

    protected function queryEmbedding()
    {
        return $this->oraculum->query($this->promptHelper->writeEmbeddedOutline(
            [
                'tone' => $this->document->getMeta('tone'),
                'keyword' => $this->document->getMeta('keyword'),
                'style' => $this->document->getMeta('style') ?? null,
                'maxsubtopics' => $this->document->getMeta('target_headers_count') ?? 2,
                'context' => $this->document->getMeta('context')
            ]
        ));
    }

    protected function queryGpt()
    {
        return $this->chatGpt->request([
            [
                'role' => 'user',
                'content' =>   $this->promptHelper->writeOutline(
                    $this->document->getContext(),
                    [
                        'tone' => $this->document->getMeta('tone'),
                        'keyword' => $this->document->getMeta('keyword'),
                        'style' => $this->document->getMeta('style') ?? null,
                        'maxsubtopics' => $this->document->getMeta('target_headers_count') ?? 2
                    ]
                )
            ]
        ]);
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'create_outline_' . $this->meta['process_id'] ?? $this->document->id;
    }
}
