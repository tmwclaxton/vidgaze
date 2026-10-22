<?php

namespace App\Helpers;

/**
 * VidGaze Picks is the Christian row on the home page: worship, sermons,
 * Christian music, Bible teaching and Christian channels.
 */
class ChristianPicks
{
    public const SLUG = 'vidgaze_picks';

    public const TAGS = ['christian', 'worship', 'sermons', 'christian music', 'bible teaching', 'gospel', 'church'];

    /** Search queries run against YouTube and Rumble. */
    public const QUERIES = [
        'worship songs live',
        'christian worship music',
        'gospel music',
        'hymns worship',
        'sunday sermon',
        'expository preaching sermon',
        'bible study teaching',
        'bible teaching book study',
        'christian testimony',
        'christian apologetics',
        'praise and worship night',
        'christian podcast faith',
    ];

    /** A video must mention one of these to count as Christian content. */
    public const KEYWORDS = [
        'christ', 'jesus', 'god', 'lord', 'bible', 'biblical', 'scripture', 'gospel',
        'worship', 'praise', 'hymn', 'sermon', 'preach', 'pastor', 'church',
        'faith', 'prayer', 'pray', 'holy spirit', 'psalm', 'apologetic', 'testimony',
        'ministry', 'ministries', 'hillsong', 'bethel', 'elevation', 'maverick city',
        'devotional', 'salvation', 'grace', 'messiah', 'chapel', 'cathedral',
    ];

    /** Categorisation hint added to the AI prompt. */
    public const DESCRIPTION = 'Christian content: worship, sermons, Christian music, Bible teaching, testimonies and Christian channels';

    public static function isChristian(?string ...$texts): bool
    {
        $haystack = mb_strtolower(implode(' ', array_filter($texts)));
        foreach (self::KEYWORDS as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '(s|es|ing|ed|er|ers|ian|ians|al|ly)?\b/u', $haystack)) {
                return true;
            }
        }
        return false;
    }
}
