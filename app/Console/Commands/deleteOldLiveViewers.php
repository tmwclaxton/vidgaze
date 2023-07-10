<?php

namespace App\Console\Commands;

use App\Models\LiveClient;
use App\Models\Stream;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class deleteOldLiveViewers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:old_live_viewers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is used to delete from the live_clients_table any records that are older than 1 minute';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $liveClients = LiveClient::where('updated_at', '<=', Carbon::now()->subMinute(1)->toDateTimeString())->take(100)->get();

        foreach ($liveClients as $liveClient) {
            if ($liveClient->type === 'video') {
                $video = Video::find($liveClient->item_id);
                if ($video) {
                    if ( $video->live_viewer_count > 0) {
                        $video->increment('live_viewer_count', -1);
                    }
                    $video->save();
                }
            } elseif ($liveClient->type === 'stream') {
                $stream = Stream::find($liveClient->item_id);
                if ($stream) {
                    if ( $stream->live_viewer_count > 0) {
                        $stream->increment('live_viewer_count', -1);
                    }
                    $stream->save();
                }
            }

            $liveClient->delete();
        }

        return Command::SUCCESS;
    }

}
