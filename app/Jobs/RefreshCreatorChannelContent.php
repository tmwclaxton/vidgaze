<?php

namespace App\Jobs;

use App\Models\CreatorModels\Creator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshCreatorChannelContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(public int $creatorId) {}

    public function handle(): void
    {
        $creator = Creator::find($this->creatorId);
        if ($creator === null) {
            return;
        }

        try {
            $creator->updateAllContentByApi();
        } catch (Throwable $e) {
            Log::warning('RefreshCreatorChannelContent failed', [
                'creator_id' => $this->creatorId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
