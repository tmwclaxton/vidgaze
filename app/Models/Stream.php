<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Str;

class Stream extends Model
{
    use HasFactory;

    //eager load creator
    protected $with = ['creator'];
    protected $attributes = [
        'viewers' => 0
    ];

    //no mass assignment!
    protected $guarded = [];

    //this is being used in the frontend and because I can't use functions in the frontend I have to use this
    public function frontEndDetails(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'language' => $this->language,
            'is_live' => $this->is_live,
            'tags' => $this->tags,
            'category' => $this->category()->first()->name,
            'preferred_source' => $this->preferred_source,
            'viewers' =>  number_format_short($this->viewers) . " " . Str::plural('Viewer', $this->viewers) ,
            'live_viewer_count' => number_format_short($this->live_viewer_count),
            'thumbnail_url' => $this->thumbnail_url,
            'creator' => $this->creator()->first()->frontEndDetails(),
        ];
    }


    //Alphabetical order

    public function creator() {
    return $this->belongsTo(Creator::class, 'creator_id');
    }
    public function sources() {
        return $this->hasMany(StreamSource::class, 'stream_id');
    }
    public function getPrimarySourceID() {
        return $this->sources->where('source_name', $this->preferred_source)->first()['external_id'];
    }
    public function awards() {
        return $this->hasMany(StreamAward::class);
    }
    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
