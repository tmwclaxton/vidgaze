<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Anonymous activity row: type, page name, optional video id or cleaned search term. */
class SiteEvent extends Model
{
    public const UPDATED_AT = null;

    public const PAGE_VIEW = 'page_view';
    public const WATCH = 'watch';
    public const SEARCH = 'search';

    protected $fillable = ['type', 'page', 'video_id', 'term'];
}
