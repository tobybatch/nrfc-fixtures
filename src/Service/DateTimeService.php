<?php

namespace App\Service;

use DateMalformedStringException;
use DateTimeImmutable;
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