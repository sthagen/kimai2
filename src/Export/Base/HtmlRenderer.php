<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Export\Base;

use App\Entity\MetaTableTypeInterface;
use App\Event\ActivityMetaDisplayEvent;
use App\Event\CustomerMetaDisplayEvent;
use App\Event\MetaDisplayEventInterface;
use App\Event\ProjectMetaDisplayEvent;
use App\Event\TimesheetMetaDisplayEvent;
use App\Event\UserPreferenceDisplayEvent;
use App\Export\ExportItemInterface;
use App\Repository\Query\CustomerQuery;
use App\Repository\Query\TimesheetQuery;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class HtmlRenderer
{
    use RendererTrait;

    /**
     * @var Environment
     */
    protected $twig;
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(Environment $twig, EventDispatcherInterface $dispatcher)
    {
        $this->twig = $twig;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param MetaDisplayEventInterface $event
     * @return MetaTableTypeInterface[]
     */
    protected function findMetaColumns(MetaDisplayEventInterface $event): array
    {
        $this->dispatcher->dispatch($event);

        return $event->getFields();
    }

    protected function getOptions(TimesheetQuery $query): array
    {
        $decimal = false;
        if (null !== $query->getCurrentUser()) {
            $decimal = (bool) $query->getCurrentUser()->getPreferenceValue('timesheet.export_decimal', $decimal);
        } elseif (null !== $query->getUser()) {
            $decimal = (bool) $query->getUser()->getPreferenceValue('timesheet.export_decimal', $decimal);
        }

        return ['decimal' => $decimal];
    }

    /**
     * @param ExportItemInterface[] $timesheets
     * @param TimesheetQuery $query
     * @return Response
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(array $timesheets, TimesheetQuery $query): Response
    {
        /** @var CustomerQuery $customerQuery */
        $customerQuery = $query->copyTo(new CustomerQuery());

        $timesheetMetaFields = $this->findMetaColumns(new TimesheetMetaDisplayEvent($query, TimesheetMetaDisplayEvent::EXPORT));
        $customerMetaFields = $this->findMetaColumns(new CustomerMetaDisplayEvent($customerQuery, CustomerMetaDisplayEvent::EXPORT));
        $projectMetaFields = $this->findMetaColumns(new ProjectMetaDisplayEvent($query, ProjectMetaDisplayEvent::EXPORT));
        $activityMetaFields = $this->findMetaColumns(new ActivityMetaDisplayEvent($query, ActivityMetaDisplayEvent::EXPORT));

        $event = new UserPreferenceDisplayEvent(UserPreferenceDisplayEvent::EXPORT);
        $this->dispatcher->dispatch($event);
        $userPreferences = $event->getPreferences();

        $content = $this->twig->render('export/renderer/default.html.twig', array_merge([
            'entries' => $timesheets,
            'query' => $query,
            'summaries' => $this->calculateSummary($timesheets),
            'timesheetMetaFields' => $timesheetMetaFields,
            'customerMetaFields' => $customerMetaFields,
            'projectMetaFields' => $projectMetaFields,
            'activityMetaFields' => $activityMetaFields,
            'userPreferences' => $userPreferences,
        ], $this->getOptions($query)));

        $response = new Response();
        $response->setContent($content);

        return $response;
    }

    public function getId(): string
    {
        return 'html';
    }
}
