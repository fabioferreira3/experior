<?php

namespace App\Jobs;

use App\Models\TextRequest;
use App\Packages\ChatGPT\ChatGPT;
use App\Repositories\TextRequestRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class BloggifyText implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public TextRequestRepository $repo;
    public TextRequest $textRequest;
    public ChatGPT $chatGpt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(TextRequest $textRequest)
    {
        $this->repo = new TextRequestRepository();
        $this->textRequest = $textRequest->fresh();
        $this->chatGpt = new ChatGPT();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (Str::wordCount($this->textRequest->original_text) < 150) {
            throw new \Exception('Text is too short');
        }

        if (!$this->textRequest->summary) {
            if ($this->textRequest->original_text_word_count > 2000) {
                $this->repo->generateSummary($this->textRequest);
            }
        }

        if (!$this->textRequest->outline) {
            $this->repo->generateOutline($this->textRequest);
            $this->increaseProgressBy(20);
        }

        if (!$this->textRequest->final_text) {
            $this->repo->createFirstPass($this->textRequest);
            $this->increaseProgressBy(25);
            $this->repo->expandText($this->textRequest);
            $this->increaseProgressBy(25);
        }

        if (!$this->textRequest->meta_description) {
            $this->repo->generateMetaDescription($this->textRequest);
            $this->increaseProgressBy(15);
        }

        if (!$this->textRequest->title) {
            $this->repo->generateTitle($this->textRequest);
            $this->increaseProgressBy(15);
        }
    }

    public function increaseProgressBy(int $amount)
    {
        $this->textRequest->update(['progress' => $this->textRequest->progress + $amount]);
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'bloggify_text_' . $this->textRequest->id;
    }
}
