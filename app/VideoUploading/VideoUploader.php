<?php

namespace App\VideoUploading;


interface VideoUploader
{
    /**
     * @param array $data
     * =(
        'title'
        'description'
        'video_tmp' => $_FILES['video_file']['tmp_name'],
        'video_name' => $_FILES['video_file']['name'],
        'thumbnail_path'
        'thumbnail_MIME'
        'made_For_Kids' (bool)
        'privacy_Status'
        'tags' (array)
        'publish_At'
        'category_id'
         'time_zone' (eg "+00:00")
        );
     * @return mixed
     */
    public static function uploadVideo(Array $data);


}
