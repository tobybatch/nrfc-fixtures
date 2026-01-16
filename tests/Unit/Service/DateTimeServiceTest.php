<?php

namespace App\Tests\Unit\Service;

use App\Service\DateTimeService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DateTimeServiceTest extends TestCase
{
    private DateTimeService $service;

    protected function setUp(): void
    {
        $this->service = new DateTimeService();
    }

    /**
     * @dataProvider provideSeasonDates
     * @throws \DateMalformedStringException
     */
    public function testGetCurrentSeason(string $inputDate, string $expectedStart, string $expectedEnd): void
    {
        $date = new DateTimeImmutable($inputDate);
        [$start, $end] = $this->service->getCurrentSeason($date);

        $this->assertEquals($expectedStart, $start->format('Y-m-d H:i:s'));
        $this->assertEquals($expectedEnd, $end->format('Y-m-d H:i:s'));
    }

    public static function provideSeasonDates(): array
    {
        return [
            'Before cutoff (May 6th)' => [
                '2024-05-06',
                '2023-08-01 00:00:00',
                '2024-05-07 23:59:59'
            ],
            'On cutoff (May 7th)' => [
                '2024-05-07 12:00:00',
                '2023-08-01 00:00:00',
                '2024-05-07 23:59:59'
            ],
            'After cutoff (May 8th)' => [
                '2024-05-08',
                '2024-08-01 00:00:00',
                '2025-05-07 23:59:59'
            ],
            'Late in the year' => [
                '2024-12-25',
                '2024-08-01 00:00:00',
                '2025-05-07 23:59:59'
            ],
        ];
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testGetAllTheMondays(): void
    {
        $start = new DateTimeImmutable('2024-01-01'); // Monday
        $end = new DateTimeImmutable('2024-01-15');   // Monday

        $mondays = $this->service->getAllThe(1, $start, $end);

        $this->assertCount(3, $mondays);
        $this->assertEquals('2024-01-01', $mondays[0]->format('Y-m-d'));
        $this->assertEquals('2024-01-08', $mondays[1]->format('Y-m-d'));
        $this->assertEquals('2024-01-15', $mondays[2]->format('Y-m-d'));
    }

    /**
     * @dataProvider provideUkDates
     * @throws \DateMalformedStringException
     */
    public function testParseUkDate(string $input, string $expectedIso): void
    {
        $result = $this->service->parseUkDateWithOptionalTime($input);
        $this->assertEquals($expectedIso, $result->format('Y-m-d H:i:s'));
    }

    public static function provideUkDates(): array
    {
        return [
            'Date only' => ['25/12/2024', '2024-12-25 00:00:00'],
            'Date with colon time' => ['14/02/2024 15:30:05', '2024-02-14 15:30:05'],
            'Date with dot time' => ['14/02/2024 15.30.05', '2024-02-14 15:30:05'],
            'Date with short time' => ['01/01/2024 9:15', '2024-01-01 09:15:00'],
        ];
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testParseInvalidUkDateThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->parseUkDateWithOptionalTime('32/01/2024'); // Invalid day
    }
}