<?php

namespace App\Jobs\SocialMedia;

use App\Enums\DataType;
use App\Enums\DocumentTaskEnum;
use App\Enums\SourceProvider;
use App\Jobs\DispatchDocumentTasks;
use App\Repositories\DocumentRepository;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ProcessSocialMediaPosts
{
    use Dispatchable, SerializesModels;

    protected $document;
    protected $platforms;
    protected $repo;
    protected string $processId;

    public function __construct($document, array $platforms)
    {
        $this->document = $document;
        $this->platforms = collect($platforms)->filter();
        $this->processId = Str::uuid();
        $this->repo = new DocumentRepository();
        $this->repo->setDocument($document);
    }

    public function handle()
    {
        $queryEmbedding = false;
        DocumentRepository::createTask(
            $this->document->id,
            DocumentTaskEnum::REMOVE_EMBEDDINGS,
            [
                'process_id' => $this->processId,
                'meta' => [
                    'collection_name' => $this->document->id
                ],
                'order' => 1
            ]
        );

        if ($this->document->meta['source'] === SourceProvider::FREE_TEXT->value) {
            if (isset($this->document->meta['context']) && Str::wordCount($this->document->getMeta('context')) > 1000) {
                $queryEmbedding = true;
                DocumentRepository::createTask(
                    $this->document->id,
                    DocumentTaskEnum::EMBED_SOURCE,
                    [
                        'process_id' => $this->processId,
                        'meta' => [
                            'data_type' => DataType::TEXT,
                            'source' => $this->document->getMeta('context'),
                            'collection_name' => $this->document->id
                        ],
                        'order' => 2
                    ]
                );
            }
        } elseif ($this->document->meta['source'] === SourceProvider::WEBSITE_URL->value) {
            foreach ($this->document->getMeta('source_urls') as $key => $sourceUrl) {
                DocumentRepository::createTask(
                    $this->document->id,
                    DocumentTaskEnum::EMBED_SOURCE,
                    [
                        'process_id' => $this->processId,
                        'meta' => [
                            'data_type' => DataType::WEB_PAGE,
                            'source' => $sourceUrl,
                            'collection_name' => $this->document->id
                        ],
                        'order' => 2 + $key
                    ]
                );
            }
        } elseif ($this->document->meta['source'] === SourceProvider::YOUTUBE->value) {
            foreach ($this->document->getMeta('source_urls') as $key => $sourceUrl) {
                DocumentRepository::createTask(
                    $this->document->id,
                    DocumentTaskEnum::EXTRACT_AND_EMBED_AUDIO,
                    [
                        'process_id' => $this->processId,
                        'meta' => [
                            'source_url' => $sourceUrl,
                            'collection_name' => $this->document->id
                        ],
                        'order' => 2 + $key
                    ]
                );
            }
        }

        DocumentRepository::createTask(
            $this->document->id,
            DocumentTaskEnum::PROCESS_SOCIAL_MEDIA_POSTS_CREATION,
            [
                'process_id' => $this->processId,
                'meta' => [
                    'platforms' => $this->platforms
                ],
                'order' => 10
            ]
        );

        DocumentRepository::createTask(
            $this->document->id,
            DocumentTaskEnum::CREATE_TITLE,
            [
                'process_id' => $this->processId,
                'meta' => [
                    'query_embedding' => $queryEmbedding,
                    'collection_name' => $this->document->id
                ],
                'order' => 99
            ]
        );

        DispatchDocumentTasks::dispatch($this->document);
    }
}
