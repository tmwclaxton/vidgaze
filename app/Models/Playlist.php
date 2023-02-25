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

    public function owner() {
        return $this->hasOne(Creator::class, 'id', 'creator_id');
    }

    public function videos() {
        return $this->hasManyThrough(Video::class, PlaylistVideo::class, 'playlist_id', 'id', 'id', 'video_id');
    }


    public function alterPlaylist($playlist,$video,$action)
    {   //add or remove a video from playlist
        //$action = add or remove
        $playlistVideo =  PlaylistVideo::where("playlist_id", $playlist->id)->where("video_id",  $video->id)->get()->first();
        $inPlaylist =  !(is_null( $playlistVideo ));
        if ($action == "remove" && $playlistVideo) {

            $this->removeFromPlaylist($playlistVideo, $playlist);

        } elseif($action == "add" && !$playlistVideo) {

            $this->addToPlaylist($playlist, $video);

        }

        $playlist->save();
    }

    public function removeFromPlaylist($playlistVideo,$playlist) {
        //if video is in playlist remove it
        PlaylistVideo::destroy($playlistVideo->id);
        $playlist->video_count--;
        if($playlist->videos->first()) {
            $playlist->recent_video_image = $playlist->videos->first()->thumbnail_url;
        } else {
            $playlist->recent_video_image = "";
        }
    }
    public function addToPlaylist($playlist,$video) {
        //if video is not in playlist add it
        PlaylistVideo::create(array(
            'playlist_id' => $playlist->id,
            'video_id' =>  $video->id
        ));
        $playlist->video_count++;
        $playlist->recent_video_image = $video->thumbnail_url;
    }

//    public function videos() {
//        return $this->hasMany(PlaylistVideo::class);
//    }
}
