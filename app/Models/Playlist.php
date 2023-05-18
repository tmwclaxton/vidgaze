<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    //no mass assignment!
    protected $guarded = [];

    //Alphabetical order

    public function owner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Creator::class, 'id', 'creator_id');
    }

    public function videos(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Video::class, PlaylistVideo::class, 'playlist_id', 'id', 'id', 'video_id');
    }

    public function addVideo(int $videoId)
    {
        $video = Video::find($videoId);

        // check if video exists
        if (!$video) {
            return;
        }

        // check if video is already in the playlist
        $existingPlaylistVideo = PlaylistVideo::where('playlist_id', $this->id)
            ->where('video_id', $videoId)
            ->first();

        if ($existingPlaylistVideo) {
            return;
        }

        // add video to playlist
        $playlistVideo = new PlaylistVideo();
        $playlistVideo->playlist_id = $this->id;
        $playlistVideo->video_id = $videoId;
        $playlistVideo->save();

        $this->video_count++;
        $this->recent_video_image = $video->thumbnail_url;
        $this->save();

    }

    public function removeVideo(int $videoId)
    {
        // get if record exists
        $playlistVideo = PlaylistVideo::where('playlist_id', $this->id)
            ->where('video_id', $videoId)
            ->first();

        // check if record exists
        if (!$playlistVideo) {
            return;
        }

        // delete record
        $playlistVideo->delete();

        // update playlist video count and recent video image
        $this->video_count--;
        if($this->videos->first()) {
            $this->recent_video_image = $this->videos->first()->thumbnail_url;
        } else {
            $this->recent_video_image = null;
        }
        $this->save();


    }

}
