<?php

namespace App\Helpers;

use Aws\Rekognition\RekognitionClient;
class ImageCheck
{
    public static function inapropriateImageCheck($image): bool
    {
        // set up AWS client with credentials from .env
        $client = new RekognitionClient([
            'region' =>  config('aws.aws_default_region', 'eu-west-1'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('aws.aws_access_key_id', ''),
                'secret' => config('aws.aws_secret_access_key', ''),
            ],
        ]);

        try {
            // check if image has nudity or gore
            $response = $client->detectModerationLabels([
                'Image' => [
                    'Bytes' => file_get_contents($image->getRealPath()),
                ],
                'MinConfidence' => 50,
            ]);
        } catch (\Exception $e) {
            return true;
        }

        if (count($response['ModerationLabels']) > 0) {
            return true;
        } else {
            return false;
        }
    }

}
