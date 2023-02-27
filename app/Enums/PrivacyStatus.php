<?php

namespace App\Enums;

enum PrivacyStatus: int
{
    case public = 0;
    case private = 1;
    case unlisted = 2;
    case scheduled = 3;
}
