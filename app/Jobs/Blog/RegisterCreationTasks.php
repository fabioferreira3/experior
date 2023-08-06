<?php

namespace App\Jobs\Blog;

use App\Enums\DocumentTaskEnum;
use App\Models\Document;
use App\Repositories\DocumentRepository;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegisterCreationTasks
{
    use Dispatchable, SerializesModels;

    public Document $document;
    protected $repo;
    protected array $params;

    public function __construct(Document $document, array $params)
    {
        $this->document = $document;
        $this->params = $params;
        $this->repo = new DocumentRepository($document);
    }

    public function handle()
    {
        $this->repo->createTask(DocumentTaskEnum::SUMMARIZE_DOC, [
            'order' => $this->params['next_order'],
            'process_id' => $this->params['process_id']
        ]);
        $this->repo->createTask(DocumentTaskEnum::CREATE_OUTLINE, [
            'process_id' => $this->params['process_id'],
            'meta' => [],
            'order' => $this->params['next_order'] + 1
        ]);
        $this->repo->createTask(DocumentTaskEnum::EXPAND_OUTLINE, [
            'process_id' => $this->params['process_id'],
            'meta' => [],
            'order' => $this->params['next_order'] + 2
        ]);
        $this->repo->createTask(
            DocumentTaskEnum::EXPAND_TEXT,
            [
                'process_id' => $this->params['process_id'],
                'meta' => [],
                'order' => $this->params['next_order'] + 3
            ]
        );
        $this->repo->createTask(
            DocumentTaskEnum::CREATE_TITLE,
            [
                'process_id' => $this->params['process_id'],
                'meta' => [],
                'order' => $this->params['next_order'] + 4
            ]
        );
        $this->repo->createTask(
            DocumentTaskEnum::CREATE_METADESCRIPTION,
            [
                'process_id' => $this->params['process_id'],
                'meta' => [],
                'order' => $this->params['next_order'] + 5
            ]
        );
    }
}
