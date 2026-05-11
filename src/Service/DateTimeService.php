<?php

namespace App\Service;

use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class DateTimeService
{
    /**
     * @throws DateMalformedStringException
     * @throws InvalidArgumentException
     */
    function parseUkDateWithOptionalTime(string $dateString): DateTimeImmutable
    {
        // Define possible patterns (UK date format first)
        $patterns = [
            // Date with time (various time separators)
            '~^(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})(?:\s+(?P<hour>\d{1,2})(?::(?P<minute>\d{2})(?::(?P<second>\d{2}))?)?)?$~',
            '~^(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})(?:\s+(?P<hour>\d{1,2})(?:\.(?P<minute>\d{2})(?:\.(?P<second>\d{2}))?)?)?$~',

            // Just date
            '~^(?P<day>\d{2})/(?P<month>\d{2})/(?P<year>\d{4})$~'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, trim($dateString), $matches)) {
                // Extract components with defaults for time
                $day = intval($matches['day']);
                $month = intval($matches['month']);
                $year = intval($matches['year']);

                $hour = in_array('hour', $matches) ? intval($matches['hour']) : 0;
                $minute = in_array('minute', $matches) ? intval($matches['minute']) : 0;
                $second = in_array('second', $matches) ? intval($matches['second']) : 0;

                // Validate the date components
                if (!checkdate($month, $day, $year)) {
                    throw new InvalidArgumentException("Invalid date: {$day}/{$month}/{$year}");
                }

                // Create ISO format string
                $isoFormat = sprintf(
                    '%04d-%02d-%02d %02d:%02d:%02d',
                    $year,
                    $month,
                    $day,
                    $hour,
                    $minute,
                    $second
                );

                return new DateTimeImmutable($isoFormat);
            }
        }

        throw new InvalidArgumentException("Invalid UK date format: {$dateString}");
    }

    /**
     * @return array<DateTimeImmutable>
     */
    function getCurrentSeason(DateTimeImmutable $date = null): array
    {
        $date = $date ?? new DateTimeImmutable('today');

        // May 7th of the current year
        $cutoff = $date->setDate((int)$date->format('Y'), 5, 7)->setTime(23, 59, 59);

        if ($date > $cutoff) {
            // After May 7th: Season starts Aug 1st this year, ends May 7th next year
            $startYear = (int)$date->format('Y');
        } else {
            // On or before May 7th: Season started Aug 1st last year, ends May 7th this year
            $startYear = (int)$date->format('Y') - 1;
        }

        return [
            $date->setDate($startYear, 8, 1)->setTime(0, 0, 0),
            $date->setDate($startYear + 1, 5, 7)->setTime(23, 59, 59),
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    function getSeasonDates(DateTimeInterface|string|null $currentDate = null): array {
        // Use current date if none provided
        if (!$currentDate) {
            $currentDate = new DateTimeImmutable();
        } elseif (is_string($currentDate)) {
            $currentDate = new DateTimeImmutable($currentDate);
        } elseif ($currentDate instanceof DateTime) {
            $currentDate = DateTimeImmutable::createFromMutable($currentDate);
        }

        $currentYear = (int) $currentDate->format('Y');
        $currentMonth = (int) $currentDate->format('m');
        $currentDay = (int) $currentDate->format('d');

        // Convert current date to comparable integer (month * 100 + day)
        $currentMonthDay = $currentMonth * 100 + $currentDay;

        $seasonStartMonthDay = 801;  // August 1st -> 801
        $seasonEndMonthDay   = 510;  // May 10th   -> 510

        // Scenario 1: May 10th (510) to August 1st (801) of the same year -> both future dates
        if ($currentMonthDay >= $seasonEndMonthDay && $currentMonthDay < $seasonStartMonthDay) {
            // Next season starts this coming August 1st
            $start = new DateTimeImmutable($currentYear . '-08-01');
            $end = new DateTimeImmutable(($currentYear + 1) . '-05-10');
        }
        // Scenario 2: August 1st (801) to May 10th (510) next year -> start in past, end in future
        else {
            $seasonStartYear = $currentYear;
            $seasonEndYear = $currentYear + 1;

            // Adjust if we are between Jan 1st and May 10th (beginning of calendar year)
            if ($currentMonthDay <= $seasonEndMonthDay) {
                $seasonStartYear = $currentYear - 1;
                $seasonEndYear = $currentYear;
            }

            $start = new DateTimeImmutable($seasonStartYear . '-08-01');
            $end = new DateTimeImmutable($seasonEndYear . '-05-10');
        }

        // Return as tuple (strictly ordered array with two elements)
        return [$start, $end];
    }

    /**
     * @return array<DateTimeImmutable>
     * @throws DateMalformedStringException
     */
    function getAllThe(int $dayOfWeek, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $dates = [];

        // Map numeric day (e.g., 1 for Monday) to string for relative modification
        $days = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];
        $dayName = $days[$dayOfWeek] ?? throw new \InvalidArgumentException("Invalid day of week: $dayOfWeek");

        // Move to the first occurrence of the day of the week on or after the start date
        $current = $start;
        if ((int)$current->format('N') !== $dayOfWeek) {
            $current = $current->modify("next $dayName");
        }

        // Iterate weekly until we pass the end date
        while ($current <= $end) {
            $dates[] = $current;
            $current = $current->modify('+1 week');
        }

        return $dates;
    }
}