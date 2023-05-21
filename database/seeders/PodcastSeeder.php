<?php

namespace Database\Seeders;

use App\Models\CreatorModels\Creator;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\PodcastModels\Podcast;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PodcastSeeder extends Seeder
{
    public function run()
    {

        Podcast::factory()->count(60)->create();
        $creator1 = Creator::firstOrCreate([
            'slug' => rand(0, 999999),
        ],[
            'name' => "Pints With Aquinas",
            'avatar_url' => 'https://ssl-static.libsyn.com/p/assets/f/f/e/f/ffeff39ab8cef524/R1-fWQQV-1.jpg',
            //'banner_url' => 'https://yt3.ggpht.com/t4uitEqCYaQlj89MPeJMynNpby8pDdE_qBReIY-t9a5KVaeoeVKGpUnfUGuBDgk8jtcsCGPwpA=w1060-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj',
            'bio' => json_encode('If you could sit down with St. Thomas Aquinas over a pint of beer and ask him any one question, what would it be? Every episode of Pints With Aquinas revolves around a question, a question that St. Thomas addresses in his most famous work, The Summa Theologica. So get your geek on, pull up a bar stool, and grab a cold one. Here we go!'),
            //'region' => 'usa',
            //'category_id' => $cat2->id,
        ]);

        $podcast1 = Podcast::firstOrCreate([
            'creator_id' => $creator1->id,
            'slug' => rand(0, 999999),
            'rss_url' => "https://pintswithaquinas.libsyn.com/rss",
            'thumbnail_url' => "https://ssl-static.libsyn.com/p/assets/f/f/e/f/ffeff39ab8cef524/R1-fWQQV-1.jpg",
            'title' => "Pints With Aquinas",
            'description' => "If you could sit down with St. Thomas Aquinas over a pint of beer and ask him any one question, what would it be? Every episode of Pints With Aquinas revolves around a question, a question that St. Thomas addresses in his most famous work, The Summa Theologica. So get your geek on, pull up a bar stool, and grab a cold one. Here we go!",
            //'category_id' => $cat2->id,
        ]);

        $ep1 = PodcastEpisode::firstOrCreate([
            "slug" => rand(0, 999999), // guid
        ],[
            'podcast_id' => $podcast1->id,
            //'category_id' => $cat2->id,
            "time_published" => Carbon::make("Fri, 09 Dec 2022 00:24:00 +0000"),
            "thumbnail_url" => "https://ssl-static.libsyn.com/p/assets/f/f/e/f/ffeff39ab8cef524/R1-fWQQV-1.jpg",
            "description" => "In this episode we're going to try to answer EVERY objection to four dogmas on Mary: The Mother of God; the immaculate conception; perpetual virginity, and the bodily assumption.",
            "audio_url" => "https://traffic.libsyn.com/secure/pintswithaquinas/_EVERY__Objection_to_Mary_Answered__w__William_Albrecht__Fr._Christiaan_Kappas_128_kbps.mp3?dest-id=367137",
            "title" => "*EVERY* Objection to Mary Answered 🤯 w/ William Albrecht & Fr. Christiaan Kappas",
            'duration' => "20"
            //"audio_file_type" => "audio/mpeg",
        ]);

        $ep2 = PodcastEpisode::firstOrCreate([
            "slug" => rand(0, 999999), // guid
            ],[
            'podcast_id' => $podcast1->id,
            //'category_id' => $cat2->id,
            "time_published" => Carbon::make("Fri, 09 Dec 2022 00:24:00 +0000"),
            "thumbnail_url" => "https://ssl-static.libsyn.com/p/assets/f/f/e/f/ffeff39ab8cef524/R1-fWQQV-1.jpg",
            "description" => ">Follow Cameron's podcast here: https://www.youtube.com/@cfradd Matt talks with his wife, Cameron Fradd, about what it's like living with chronic pain",
            "audio_url" => "https://traffic.libsyn.com/secure/pintswithaquinas/Cameron_January.mp3?dest-id=367137",
            "title" => "Living with Chronic Pain w/ Cameron Fradd",
            'duration' => "20"
            //"audio_file_type" => "audio/mpeg",
        ]);

        $ep3 = PodcastEpisode::firstOrCreate([
            "slug" => rand(0, 999999), // guid
            ],[
            'podcast_id' => $podcast1->id,
            //'category_id' => $cat2->id,
            "time_published" => Carbon::make("Thu, 01 Dec 2022 02:39:00 +0000"),
            "thumbnail_url" => "https://ssl-static.libsyn.com/p/assets/f/f/e/f/ffeff39ab8cef524/R1-fWQQV-1.jpg",
            "description" => "I chat with Michael Knowles of The Daily Wire about conservatism, Catholicism, and good cigars.",
            "audio_url" => "https://traffic.libsyn.com/secure/pintswithaquinas/Conservatism_Catholicism_and_Good_Cigars_w__Michael_Knowles_128_kbps.mp3?dest-id=367137",
            "title" => "Conservatism, Catholicism, and Good Cigars w/ Michael Knowless",
            'duration' => "20"
            //"audio_file_type" => "audio/mpeg",
        ]);
    }
}
