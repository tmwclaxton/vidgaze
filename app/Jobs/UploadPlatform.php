<?php

namespace App\Jobs;

use App\Enums\Platform;
use App\Helpers\UploadDTO;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class uploadPlatform implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $creator_id,
        public UploadDTO $uploadDTO,
        public Platform $platform
    ){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $source = Creator::find($this->creator_id)->sources()->where('source_name', $this->platform->value)->first();
        $source->refreshAccessToken();

        $class = $this->platform->getPlatformClass($source->access_token);
        $class->upload($this->uploadDTO);
        // check if all uploads are done
        // if so delete video and thumbnail
    }
}
