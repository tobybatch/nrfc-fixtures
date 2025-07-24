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
}