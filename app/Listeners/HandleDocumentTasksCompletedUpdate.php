<?php

namespace App\Listeners;

use App\Events\DocumentTaskFinished;
use App\Repositories\DocumentRepository;

class HandleDocumentTasksCompletedUpdate
{
    /**
     * Handle the event.
     *
     * @param DocumentTaskFinished $event
     * @return void
     */
    public function handle(DocumentTaskFinished $event)
    {
        $repo = new DocumentRepository($event->task->document);
        $totalTasks = $event->task->document->getMeta('total_tasks_count') ?? 0;
        $progress = $totalTasks > 0 ? floor(($event->completedTasksCount * 100) / $totalTasks) : 0;
        $repo->updateMeta('tasks_progress', $progress);
    }
}
