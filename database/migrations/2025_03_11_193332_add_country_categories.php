<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // array of countries and tags related to them
        $countries = [
            'Australia' => ['news', 'wildlife', 'sports', 'rugby', 'cricket', 'nature'],
            'Austria' => ['mozart', 'music', 'alpines', 'vienna', 'skiing'],
            'Belgium' => ['chocolate', 'europe', 'politics', 'EU', 'beer',],
            'China' => ['xi jinping', 'pandas', 'great wall'],
            'United States' => ['politics', 'news', 'conservative', 'trump', 'republican', 'democrat'],
            'Canada' => ['trudeau', 'tim hortons', 'hockey'],
            'United Kingdom' => ['brexit', 'politics', 'news', 'royalty', 'queen', 'labor', 'conservatives'],
            'India' => ['bollywood', 'cricket', 'politics', 'news', 'modi', 'technology'],
            'Germany' => ['engineering', 'technology', 'news', 'merkel', 'economy', 'cars'],
            'Japan' => ['technology', 'anime', 'manga', 'cars', 'economy', 'nature'],
            'Ireland' => ['drinking', 'derry girls', 'father ted', 'dublin'],
            'Northern Ireland' => ['belfast', 'londonderry', 'giants causeway', 'carrick-a-rede']
        ];

        // loop through the countries and add them to the database
        foreach ($countries as $country => $tags) {
            \App\Models\Category::create([
                'name' => $country,
                'slug' => \Illuminate\Support\Str::slug($country),
                'tags' => $tags,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
