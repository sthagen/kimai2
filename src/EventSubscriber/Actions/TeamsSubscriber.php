<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber\Actions;

use App\Event\PageActionsEvent;

class TeamsSubscriber extends AbstractActionsSubscriber
{
    public static function getActionName(): string
    {
        return 'teams';
    }

    public function onActions(PageActionsEvent $event): void
    {
        $event->addSearchToggle();

        if ($this->isGranted('create_team')) {
            $event->addCreate($this->path('admin_team_create'), false);
        }

        $event->addHelp($this->documentationLink('teams.html'));
    }
}
