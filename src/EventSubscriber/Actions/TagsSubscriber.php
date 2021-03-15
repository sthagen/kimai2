<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber\Actions;

use App\Event\PageActionsEvent;

class TagsSubscriber extends AbstractActionsSubscriber
{
    public static function getActionName(): string
    {
        return 'tags';
    }

    public function onActions(PageActionsEvent $event): void
    {
        $event->addSearchToggle();

        if ($this->isGranted('manage_tag')) {
            $event->addCreate($this->path('tags_create'));
        }

        $event->addHelp($this->documentationLink('tags.html'));
    }
}
