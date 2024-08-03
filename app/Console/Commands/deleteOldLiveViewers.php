<?php

namespace App\Console\Commands;


use App\Models\LiveClient;
use App\Models\StreamModels\Stream;
use App\Models\VideoModels\Video;
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


        if (count($liveClients) > 0) {
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
        }


        // extra code to remove fake live viewers
        $videos = Video::where('live_viewer_count', '>', 0)->get();
        foreach ($videos as $video) {
            $liveClients = LiveClient::where('item_id', $video->id)->where('type', 'video')->get();
            if (count($liveClients) < $video->live_viewer_count) {
                $diff = $video->live_viewer_count - count($liveClients);
                $video->decrement('live_viewer_count', $diff);
            }
        }

         $this->info('Successfully deleted old live viewers');

        // return success
        return 0;

    }

}
