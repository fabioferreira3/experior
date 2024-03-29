<?php

namespace App\Jobs;

use App\Helpers\SupportHelper;
use App\Jobs\Traits\JobEndings;
use App\Models\Account;
use App\Models\AppUsage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Exception;

class RegisterAppUsage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, JobEndings;

    public Account $account;
    public array $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Account $account, array $params = [])
    {
        $this->account = $account;
        $this->params = $params;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $cost = 0;
            if ($this->params['model'] ?? false) {
                $cost = SupportHelper::calculateModelCosts($this->params['model'], [
                    'prompt' => $this->params['prompt'] ?? 0,
                    'completion' => $this->params['completion'] ?? 0,
                    'audio_length' => $this->params['length'] ?? 0,
                    'char_count' => $this->params['char_count'] ?? 0,
                    'total' => $this->params['total'] ?? 0,
                    'size' => $this->params['size'] ?? '1024x1024'
                ]);
            }

            $this->account->appUsage()->save(
                new AppUsage([
                    'model' => $this->params['model'] ?? null,
                    'prompt_token_usage' => $this->params['prompt'] ?? 0,
                    'completion_token_usage' => $this->params['completion'] ?? 0,
                    'total_token_usage' => $this->params['total'] ?? 0,
                    'cost' => $cost,
                    'meta' => $this->params['meta'] ?? []
                ])
            );
            $this->jobSucceded();
        } catch (Exception $e) {
            $this->jobFailed('Failed to register product usage: ' . $e->getMessage());
        }
    }
}
