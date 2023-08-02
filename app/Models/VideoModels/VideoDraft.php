<?php

namespace App\Models\VideoModels;

use App\Models\Category;
use App\Models\CreatorModels\Creator;
use Illuminate\Database\Eloquent\Model;

class VideoDraft extends Model
{
    protected $guarded = ['id'];

    protected $with = ['creator'];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
