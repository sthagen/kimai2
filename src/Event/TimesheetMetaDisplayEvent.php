<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Event;

use App\Repository\Query\TimesheetQuery;

/**
 * Dynamically find possible meta fields for a timesheet query.
 *
 * @method TimesheetQuery getQuery()
 */
final class TimesheetMetaDisplayEvent extends AbstractMetaDisplayEvent
{
    public const EXPORT = 'export';
    public const TIMESHEET = 'timesheet';
    public const TEAM_TIMESHEET = 'team-timesheet';
    public const TIMESHEET_EXPORT = 'timesheet-export';
    public const TEAM_TIMESHEET_EXPORT = 'team-timesheet-export';

    public function __construct(TimesheetQuery $query, string $location)
    {
        parent::__construct($query, $location);
    }
}
