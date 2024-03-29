<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class DispatchDocumentTasks
{
    use Dispatchable, SerializesModels;

    public Document $document;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Document $document)
    {
        $this->document = $document->fresh();
    }

    public function handle()
    {
        $tasks = $this->document->tasks()->available()->priorityFirst()->get();
        if (!$tasks->count()) {
            return;
        }

        DB::table('document_tasks')->whereIn('id', $tasks->pluck('id')->toArray())->update(['status' => 'in_progress']);

        $tasksByProcess = $tasks->groupBy('process_id')->all();

        foreach ($tasksByProcess as $processTasks) {
            $jobsChain = [];
            foreach ($processTasks as $task) {
                $class = $task->job;
                $jobsChain[] = new $class($task->document, [
                    ...$task->meta,
                    'task_id' => $task->id,
                    'process_id' => $task->process_id,
                    'process_group_id' => $task->process_group_id,
                    'order' => $task->order,
                ]);
            }
            if (!empty($jobsChain)) {
                Bus::chain($jobsChain)->dispatch();
            }
        }
    }
}
