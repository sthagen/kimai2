<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Export\Base;

use App\Export\ExportItemInterface;
use App\Repository\Query\TimesheetQuery;
use App\Timesheet\UserDateTimeFactory;
use App\Utils\HtmlToPdfConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

class PDFRenderer
{
    use RendererTrait;

    /**
     * @var Environment
     */
    private $twig;
    /**
     * @var UserDateTimeFactory
     */
    private $dateTime;
    /**
     * @var HtmlToPdfConverter
     */
    private $converter;

    public function __construct(Environment $twig, UserDateTimeFactory $dateTime, HtmlToPdfConverter $converter)
    {
        $this->twig = $twig;
        $this->dateTime = $dateTime;
        $this->converter = $converter;
    }

    protected function getTemplate(): string
    {
        return 'export/renderer/pdf.html.twig';
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
        $content = $this->twig->render($this->getTemplate(), array_merge([
            'entries' => $timesheets,
            'query' => $query,
            'now' => $this->dateTime->createDateTime(),
            'summaries' => $this->calculateSummary($timesheets),
            'decimal' => false,
        ], $this->getOptions($query)));

        $content = $this->converter->convertToPdf($content);

        $response = new Response($content);

        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'kimai-export.pdf');

        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function getId(): string
    {
        return 'pdf';
    }
}
