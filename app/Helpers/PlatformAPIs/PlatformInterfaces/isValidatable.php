<?php

namespace App\Helpers\PlatformAPIs\PlatformInterfaces;

use App\Helpers\SearchQueryDTO;

interface isValidatable
{
    public static function validate(array $results): array;
}
