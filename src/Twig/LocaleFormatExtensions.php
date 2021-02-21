<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use App\Configuration\LanguageFormattings;
use App\Entity\Timesheet;
use App\Entity\User;
use App\Utils\LocaleFormats;
use App\Utils\LocaleFormatter;
use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

final class LocaleFormatExtensions extends AbstractExtension
{
    /**
     * @var LanguageFormattings|null
     */
    private $formats;
    /**
     * @var LocaleFormats|null
     */
    private $localeFormats;
    /**
     * @var LocaleFormatter|null
     */
    private $formatter;
    /**
     * @var string
     */
    private $locale;

    public function __construct(LanguageFormattings $formats)
    {
        $this->formats = $formats;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter('month_name', [$this, 'monthName']),
            new TwigFilter('day_name', [$this, 'dayName']),
            new TwigFilter('date_short', [$this, 'dateShort']),
            new TwigFilter('date_time', [$this, 'dateTime']),
            new TwigFilter('date_full', [$this, 'dateTimeFull']),
            new TwigFilter('date_format', [$this, 'dateFormat']),
            new TwigFilter('time', [$this, 'time']),
            new TwigFilter('hour24', [$this, 'hour24']),
            new TwigFilter('duration', [$this, 'duration']),
            new TwigFilter('duration_decimal', [$this, 'durationDecimal']),
            new TwigFilter('money', [$this, 'money']),
            new TwigFilter('currency', [$this, 'currency']),
            new TwigFilter('country', [$this, 'country']),
            new TwigFilter('language', [$this, 'language']),
            new TwigFilter('amount', [$this, 'amount']),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('weekend', function ($dateTime) {
                if (!$dateTime instanceof \DateTime) {
                    return false;
                }
                $day = (int) $dateTime->format('w');

                return ($day === 0 || $day === 6);
            }),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('get_format_duration', [$this, 'getDurationFormat']),
            new TwigFunction('create_date', [$this, 'createDate']),
            new TwigFunction('locales', [$this, 'getLocales']),
        ];
    }

    /**
     * Allows to switch the locale used for all twig filter and functions.
     *
     * @param string $locale
     */
    public function setLocale(string $locale)
    {
        $this->locale = $locale;
        $this->formatter = null;
        $this->localeFormats = null;
    }

    private function getLocaleFormats(): LocaleFormats
    {
        if (null === $this->localeFormats) {
            $this->localeFormats = new LocaleFormats($this->formats, $this->getLocale());
        }

        return $this->localeFormats;
    }

    private function getFormatter(): LocaleFormatter
    {
        if (null === $this->formatter) {
            $this->formatter = new LocaleFormatter($this->formats, $this->getLocale());
        }

        return $this->formatter;
    }

    private function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = \Locale::getDefault();
        }

        return $this->locale;
    }

    /**
     * @param DateTime|string $date
     * @return string
     */
    public function dateShort($date)
    {
        return $this->getFormatter()->dateShort($date);
    }

    /**
     * @param DateTime|string $date
     * @return string
     */
    public function dateTime($date)
    {
        return $this->getFormatter()->dateTime($date);
    }

    /**
     * @param DateTime|string $date
     * @return bool|false|string
     */
    public function dateTimeFull($date)
    {
        return $this->getFormatter()->dateTimeFull($date);
    }

    public function createDate(string $date, ?User $user = null): \DateTime
    {
        $timezone = $user !== null ? $user->getTimezone() : date_default_timezone_get();

        return new DateTime($date, new \DateTimeZone($timezone));
    }

    /**
     * @param DateTime|string $date
     * @param string $format
     * @return false|string
     * @throws \Exception
     */
    public function dateFormat($date, string $format)
    {
        return $this->getFormatter()->dateFormat($date, $format);
    }

    /**
     * @param DateTime|string $date
     * @return string
     */
    public function time($date)
    {
        return $this->getFormatter()->time($date);
    }

    public function monthName(\DateTime $dateTime, bool $withYear = false): string
    {
        return $this->getFormatter()->monthName($dateTime, $withYear);
    }

    public function dayName(\DateTime $dateTime, bool $short = false): string
    {
        return $this->getFormatter()->dayName($dateTime, $short);
    }

    /**
     * @param mixed $twentyFour
     * @param mixed $twelveHour
     * @return mixed
     */
    public function hour24($twentyFour, $twelveHour)
    {
        return $this->getFormatter()->hour24($twentyFour, $twelveHour);
    }

    public function getDurationFormat(): string
    {
        return $this->getLocaleFormats()->getDurationFormat();
    }

    /**
     * Transforms seconds into a duration string.
     *
     * @param int|Timesheet|null $duration
     * @param bool $decimal
     * @return string
     */
    public function duration($duration, $decimal = false)
    {
        return $this->getFormatter()->duration($duration, $decimal);
    }

    /**
     * Transforms seconds into a decimal formatted duration string.
     *
     * @param int|Timesheet|null $duration
     * @return string
     */
    public function durationDecimal($duration)
    {
        return $this->getFormatter()->durationDecimal($duration);
    }

    /**
     * @param string|float $amount
     * @return bool|false|string
     */
    public function amount($amount)
    {
        return $this->getFormatter()->amount($amount);
    }

    /**
     * Returns the currency symbol.
     *
     * @param string $currency
     * @return string
     */
    public function currency($currency)
    {
        return $this->getFormatter()->currency($currency);
    }

    /**
     * @param string $language
     * @return string
     */
    public function language($language)
    {
        return $this->getFormatter()->language($language);
    }

    /**
     * @param string $country
     * @return string
     */
    public function country($country)
    {
        return $this->getFormatter()->country($country);
    }

    /**
     * @param float $amount
     * @param string|null $currency
     * @param bool $withCurrency
     * @return string
     */
    public function money($amount, ?string $currency = null, bool $withCurrency = true)
    {
        return $this->getFormatter()->money($amount, $currency, $withCurrency);
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.)
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->getFormatter()->getLocales();
    }
}
