<?php

namespace App\Jobs;

use App\Enums\Platform;
use App\Helpers\UploadDTO;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoSource;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadPlatform implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $creator_id,
        public int $video_id,
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
        $external_video_id = $this->platform->getPlatformAuthObject($source->access_token)->upload($this->uploadDTO);

        $video = Video::find($this->video_id);
        $video->sources()->create([
            'source_name' => $this->platform->value,
            'external_id' => $external_video_id
        ]);

        if($video->sources()->count() === sizeof($this->uploadDTO->platforms)) {
            unlink(storage_path('app/' . $this->uploadDTO->video_path));
            unlink(storage_path('app/public/' . $this->uploadDTO->thumbnail_path));
        }
    }
}
