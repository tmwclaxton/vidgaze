<?php

use App\Models\Award;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $awardFilePath = "/images/awards/";
        $awardFileType = ".png";
        //All awards
        $award1 = Award::updateOrCreate(['name' => 'Gold'],[
            'name' => 'Gold',
            'description' => 'Reserved for only the best posts!',
            'icon_url' => $awardFilePath.'GoldAward'.'.png',
            'coin_price' => '1000',
            'gifted_coins' => '40',
        ]);

        $award2 = Award::updateOrCreate(['name' => 'Silver'],[
            'name' => 'Silver',
            'description' => 'Behold the Silver Medal',
            'icon_url' => $awardFilePath.'SilverAward'.$awardFileType,
            'coin_price' => '500',
            'gifted_coins' => '0',
        ]);
        $awards = [
            [
                'name' => 'Platinum',
                'description' => 'Unironically better than the Gold Award',
                'icon_url' => $awardFilePath.'DiamondPlatinumAward'.$awardFileType,
                'coin_price' => '2000',
                'gifted_coins' => '0',
            ],[
                'name' => 'Supernova',
                'description' => 'Star go boom...',
                'icon_url' => $awardFilePath.'SupernovaAward'.$awardFileType,
                'coin_price' => '7500',
                'gifted_coins' => '0',
            ],[
                'name' => 'Flaming Heart',
                'description' => 'My heart will go on...',
                'icon_url' => $awardFilePath.'FlamingHeart'.$awardFileType,
                'coin_price' => '150',
                'gifted_coins' => '0',
            ],[
                'name' => 'Point Upwards',
                'description' => 'This one right here.',
                'icon_url' => $awardFilePath.'Upvote'.$awardFileType,
                'coin_price' => '50',
                'gifted_coins' => '0',
            ],[
                'name' => 'Onion',
                'description' => 'Just an onion',
                'icon_url' => $awardFilePath.'OnionBase'.$awardFileType,
                'coin_price' => '300',
                'gifted_coins' => '0',
            ],
            [
                'name' => 'Angry Onion',
                'description' => 'He\'s pissed off.  He ain\'t messing around.',
                'icon_url' => $awardFilePath.'OnionFiredUp'.$awardFileType,
                'coin_price' => '300',
                'gifted_coins' => '0',
            ],
            [
                'name' => 'OMG AN ONION',
                'description' => 'IS LIFE EVEN REAL!!!',
                'icon_url' => $awardFilePath.'OnionWOW'.$awardFileType,
                'coin_price' => '300',
                'gifted_coins' => '0',
            ],
            [
                'name' => 'Laughter',
                'description' => 'Laughing is healthy bro',
                'icon_url' => $awardFilePath.'MascotLaughing'.$awardFileType,
                'coin_price' => '250',
                'gifted_coins' => '0',
            ],[
                'name' => 'In love',
                'description' => 'I\'ll find someone...',
                'icon_url' => $awardFilePath.'MascotHearteyes'.$awardFileType,
                'coin_price' => '250',
                'gifted_coins' => '0',
            ],[
                'name' => 'Let it out',
                'description' => 'Don\'t keep it bottled in',
                'icon_url' => $awardFilePath.'MascotCrying'.$awardFileType,
                'coin_price' => '250',
                'gifted_coins' => '0',
            ],[//
                'name' => 'hugz',
                'description' => 'I demand hugs now...',
                'icon_url' => $awardFilePath.'MascotHuggingAlien'.$awardFileType,
                'coin_price' => '10',
                'gifted_coins' => '0',
            ],[
                'name' => 'Rocket go brrrrr',
                'description' => 'Boldly go where we haven\'t been in a long, long time.',
                'icon_url' => $awardFilePath.'Rocket'.$awardFileType,
                'coin_price' => '750',
                'gifted_coins' => '0',
            ],[
                'name' => 'Space Cat',
                'description' => 'vibin',
                'icon_url' => $awardFilePath.'SpaceCat'.$awardFileType,
                'coin_price' => '350',
                'gifted_coins' => '0',
            ],[
                'name' => 'Big rock go boom!',
                'description' => 'Don\'t look up...',
                'icon_url' => $awardFilePath.'Meteor'.$awardFileType,
                'coin_price' => '500',
                'gifted_coins' => '0',
            ]];
        foreach($awards as $award) {
            $existingAward = Award::where('name', $award['name'])->first();
            if(!$existingAward) {
                Award::create($award);
            }
        }

    }
};
