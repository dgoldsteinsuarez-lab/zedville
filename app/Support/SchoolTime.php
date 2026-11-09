<?php

namespace App\Support;

use App\Models\SchoolDomain;
use Carbon\Carbon;

/**
 * A school's own time zone (school_domains.timezone) - so things like the
 * greeting follow the school's clock, not the server's (UTC).
 */
class SchoolTime
{
    public const DEFAULT = 'Europe/Berlin';

    public static function timezoneFor($user): string
    {
        $timezone = $user && $user->sid ? SchoolDomain::whereKey($user->sid)->value('timezone') : null;

        return $timezone && in_array($timezone, timezone_identifiers_list(), true) ? $timezone : self::DEFAULT;
    }

    public static function now($user): Carbon
    {
        return now(self::timezoneFor($user));
    }

    /** Time zones grouped by region, for the admin school form. */
    public static function options(): array
    {
        $groups = [];
        foreach (timezone_identifiers_list() as $identifier) {
            $region = strstr($identifier, '/', true) ?: 'Other';
            $groups[$region][] = $identifier;
        }

        return $groups;
    }
}
