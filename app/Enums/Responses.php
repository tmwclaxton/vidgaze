<?php

namespace App\Enums;

use InvalidArgumentException;

enum Responses: string
{
    case ROLE_UPDATED = "User Role Updated Successfully";
    case ROLE_UPDATE_FAILED = "User Role Update Failed";
    case UNAUTHORISED = "Unauthorised Access";
    const MODERATOR_LIST_FAILED = "Failed to list moderators";
}
