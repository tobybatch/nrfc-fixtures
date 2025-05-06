<?php

namespace App\DataFixtures;

use App\Config\Team;
use App\Service\FixtureService;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class Fixtures extends Fixture
{
    private FixtureService $fixtureService;

    public function __construct(FixtureService $fixtureService)
    {
        $this->fixtureService = $fixtureService;
    }

    public function getDependencies(): array
    {
        return [
            Fixtures::class,
        ];
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->rows as $row) {
            $date = DateTimeImmutable::createFromMutable(
                DateTime::createFromFormat('j-M-y', $row['date'])
            )->setTime(0, 1, 0);
            foreach (Team::cases() as $team) {
                if ($row[$team->value]) {
                    $this->fixtureService->createFixture($team, $date, $row[$team->value]);
                }
            }
        }
    }

    private $rows = [
    [
        'date' => '7-Sep-25',
        'Minis' => 'Training',
        'U13B' => 'Training',
        'U14B' => 'Training',
        'U15B' => 'Training',
        'U16B' => 'Training',
        'U18B' => 'Training',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '14-Sep-25',
        'Minis' => 'Training',
        'U13B' => 'Training',
        'U14B' => 'Training',
        'U15B' => 'Training',
        'U16B' => 'Training',
        'U18B' => 'Training',
        'U12G' => '',
        'U14G' => 'Skills Session',
        'U16G' => 'Skills Session',
        'U18G' => ''
    ],
    [
        'date' => '21-Sep-25',
        'Minis' => 'BECCLES (A)',
        'U13B' => 'North Walsham (H)',
        'U14B' => 'North Walsham (A)',
        'U15B' => 'North Walsham (H)',
        'U16B' => 'North Walsham (A)',
        'U18B' => 'Colts Conference',
        'U12G' => 'Love Rugby Festival      (U9-U12 non-contact)',
        'U14G' => 'Love Rugby Festival',
        'U16G' => 'Love Rugby Festival',
        'U18G' => 'Love Rugby Festival'
    ],
    [
        'date' => '28-Sep-25',
        'Minis' => 'Training',
        'U13B' => 'West Norfolk (H)',
        'U14B' => 'Norfolk10s',
        'U15B' => 'Norfolk10s',
        'U16B' => 'Norfolk10s',
        'U18B' => 'Colts Conference',
        'U12G' => 'Conference',
        'U14G' => 'Conference',
        'U16G' => 'Conference',
        'U18G' => 'Conference'
    ],
    [
        'date' => '5-Oct-25',
        'Minis' => 'L&Y FESTIVAL',
        'U13B' => 'Ipswich (A)',
        'U14B' => 'Ipswich (H)',
        'U15B' => 'Ipswich (A)',
        'U16B' => 'Ipswich (H)',
        'U18B' => 'Nat Cup',
        'U12G' => '',
        'U14G' => '',
        'U16G' => 'Nat Cup',
        'U18G' => 'Nat Cup'
    ],
    [
        'date' => '12-Oct-25',
        'Minis' => 'Training',
        'U13B' => 'Stowmarket (A)',
        'U14B' => 'Stowmarket (H)',
        'U15B' => 'Training',
        'U16B' => 'Stowmarket (H)',
        'U18B' => 'Stow Festival',
        'U12G' => 'Conference',
        'U14G' => 'Conference',
        'U16G' => 'Conference',
        'U18G' => 'Conference'
    ],
    [
        'date' => '19-Oct-25',
        'Minis' => 'Under 12 festival (tbc) or Training',
        'U13B' => 'Woodbridge (H)',
        'U14B' => 'Woodbridge (A)',
        'U15B' => 'Woodbridge (H)',
        'U16B' => 'Woodbridge (A)',
        'U18B' => 'Nat Cup',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '26-Oct-25',
        'Minis' => 'WYMDONHAM FESTIVAL',
        'U13B' => 'Fakenham (A)',
        'U14B' => 'Training',
        'U15B' => 'Fakenham (H)',
        'U16B' => 'L&Y (H)',
        'U18B' => 'CB U18',
        'U12G' => '',
        'U14G' => '',
        'U16G' => 'Nat Cup',
        'U18G' => 'Nat Cup'
    ],
    [
        'date' => '2-Nov-25',
        'Minis' => 'Training',
        'U13B' => 'Training',
        'U14B' => 'Training',
        'U15B' => 'Training',
        'U16B' => 'West Norfolk (A)',
        'U18B' => 'CB U18',
        'U12G' => 'Skills Session',
        'U14G' => 'Skills Session',
        'U16G' => '',
        'U18G' => 'CB / Pathway'
    ],
    [
        'date' => '9-Nov-25',
        'Minis' => 'Training',
        'U13B' => 'Crusaders (H)',
        'U14B' => 'Bury (A)',
        'U15B' => 'Crusaders (A)',
        'U16B' => 'Crusaders (A)',
        'U18B' => 'Colts Conference',
        'U12G' => 'Conference',
        'U14G' => 'Conference',
        'U16G' => 'Conference',
        'U18G' => 'Conference'
    ],
    [
        'date' => '16-Nov-25',
        'Minis' => 'HOLT/ F&S BARB (H)',
        'U13B' => 'Newmarket (A)',
        'U14B' => 'Training',
        'U15B' => 'Newmarket (H)',
        'U16B' => 'Newmarket (H)',
        'U18B' => 'Colts Conference',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => 'CB'
    ],
    [
        'date' => '23-Nov-25',
        'Minis' => 'Training',
        'U13B' => 'Diss (A)',
        'U14B' => 'Diss (H)',
        'U15B' => 'Diss (A)',
        'U16B' => 'Diss (H)',
        'U18B' => 'CB U18',
        'U12G' => '',
        'U14G' => '',
        'U16G' => 'Nat Cup',
        'U18G' => 'Nat Cup'
    ],
    [
        'date' => '30-Nov-25',
        'Minis' => 'CRUSADERS (H)',
        'U13B' => 'EC Festival 1',
        'U14B' => 'Shelford (H)',
        'U15B' => 'Shelford (A)',
        'U16B' => 'Shelford (A)',
        'U18B' => 'Colts Conference',
        'U12G' => 'Conference',
        'U14G' => 'Conference',
        'U16G' => 'Conference',
        'U18G' => 'Conference'
    ],
    [
        'date' => '7-Dec-25',
        'Minis' => 'Training',
        'U13B' => 'Training',
        'U14B' => 'Training',
        'U15B' => 'Beccles (A)',
        'U16B' => 'Beccles (A)',
        'U18B' => 'CB U18',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => 'CB'
    ],
    [
        'date' => '14-Dec-25',
        'Minis' => 'Training',
        'U13B' => 'Shelford (H)',
        'U14B' => 'County Cup',
        'U15B' => 'County Cup',
        'U16B' => 'County Cup',
        'U18B' => 'Colts Conference',
        'U12G' => '',
        'U14G' => 'Academy',
        'U16G' => 'Academy',
        'U18G' => 'Pathway'
    ],
    [
        'date' => '21-Dec-25',
        'Minis' => 'Christmas',
        'U13B' => 'Christmas',
        'U14B' => 'Christmas',
        'U15B' => 'Christmas',
        'U16B' => 'Christmas',
        'U18B' => 'CB U18',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '28-Dec-25',
        'Minis' => 'Christmas',
        'U13B' => 'Christmas',
        'U14B' => 'Christmas',
        'U15B' => 'Christmas',
        'U16B' => 'Christmas',
        'U18B' => 'Christmas',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '4-Jan-26',
        'Minis' => 'Training',
        'U13B' => 'Haverhill (H)',
        'U14B' => 'Haverhill (A)',
        'U15B' => 'Training',
        'U16B' => 'Haverhill (A)',
        'U18B' => 'Christmas',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '11-Jan-26',
        'Minis' => 'W NORFOLK (A)',
        'U13B' => 'Diss/Wisbech (A)',
        'U14B' => 'County Cup',
        'U15B' => 'County Cup',
        'U16B' => 'County Cup',
        'U18B' => 'Colts Cup',
        'U12G' => 'Conference',
        'U14G' => 'Conference',
        'U16G' => 'Conference',
        'U18G' => 'Conference'
    ],
    [
        'date' => '18-Jan-26',
        'Minis' => 'Training',
        'U13B' => 'Training',
        'U14B' => 'County Cup',
        'U15B' => 'County Cup',
        'U16B' => 'County Cup',
        'U18B' => 'Colts Cup',
        'U12G' => '',
        'U14G' => '',
        'U16G' => 'Nat Cup',
        'U18G' => 'CB'
    ],
    [
        'date' => '25-Jan-26',
        'Minis' => 'L&Y (A)',
        'U13B' => 'EC Festival 2',
        'U14B' => 'Colchester (A)',
        'U15B' => 'Training',
        'U16B' => 'Training',
        'U18B' => '',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => 'Pathway'
    ],
    [
        'date' => '1-Feb-26',
        'Minis' => 'Training',
        'U13B' => 'Wymondham (H)',
        'U14B' => 'County Cup',
        'U15B' => 'County Cup',
        'U16B' => 'County Cup',
        'U18B' => 'Colts Cup',
        'U12G' => 'Conference',
        'U14G' => 'Conference',
        'U16G' => 'Conference',
        'U18G' => 'Conference'
    ],
    [
        'date' => '8-Feb-26',
        'Minis' => 'NORTH WALSHAM (A)',
        'U13B' => 'Diss/Beccles (A)',
        'U14B' => 'County Cup',
        'U15B' => 'County Cup',
        'U16B' => 'County Cup',
        'U18B' => 'Colts Cup',
        'U12G' => '',
        'U14G' => 'Academy',
        'U16G' => 'Academy',
        'U18G' => 'Nat Cup'
    ],
    [
        'date' => '15-Feb-26',
        'Minis' => 'Training',
        'U13B' => 'Holt (H)',
        'U14B' => 'Training',
        'U15B' => 'Holt (A)',
        'U16B' => 'Training',
        'U18B' => '',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => 'CB / Pathway'
    ],
    [
        'date' => '22-Feb-26',
        'Minis' => 'Training',
        'U13B' => 'Training',
        'U14B' => 'Stowmarket (A)',
        'U15B' => 'Training',
        'U16B' => 'Stowmarket (A)',
        'U18B' => '',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '1-Mar-26',
        'Minis' => 'SOUTHWOLD (H)',
        'U13B' => 'Training',
        'U14B' => 'Woodbridge (H)',
        'U15B' => 'Training',
        'U16B' => 'Training',
        'U18B' => '',
        'U12G' => 'Conference',
        'U14G' => 'Conference',
        'U16G' => 'Nat Cup / Conference',
        'U18G' => 'Nat Cup / Conference'
    ],
    [
        'date' => '8-Mar-26',
        'Minis' => 'Under 12 FESTIVAL (tbc) / training',
        'U13B' => 'Haverhill (A)',
        'U14B' => 'Haverhill (H)',
        'U15B' => 'Newmarket (A)',
        'U16B' => 'Haverhill (H)',
        'U18B' => '',
        'U12G' => 'Festival',
        'U14G' => 'Festival',
        'U16G' => 'Festival',
        'U18G' => 'Festival / Pathway'
    ],
    [
        'date' => '15-Mar-26',
        'Minis' => 'Mothering Sunday',
        'U13B' => 'Mothering Sunday',
        'U14B' => 'Mothering Sunday',
        'U15B' => 'Mothering Sunday',
        'U16B' => 'Mothering Sunday',
        'U18B' => 'Mothering Sunday',
        'U12G' => '',
        'U14G' => 'Academy',
        'U16G' => 'Academy',
        'U18G' => ''
    ],
    [
        'date' => '22-Mar-26',
        'Minis' => 'DISS /F&S BARB (A)',
        'U13B' => 'Bury (A)',
        'U14B' => 'County Cup',
        'U15B' => 'County Cup',
        'U16B' => 'County Cup',
        'U18B' => '',
        'U12G' => '',
        'U14G' => '',
        'U16G' => 'Nat Cup',
        'U18G' => 'Nat Cup'
    ],
    [
        'date' => '29-Mar-26',
        'Minis' => 'DISS FESTIVAL',
        'U13B' => 'Training',
        'U14B' => 'Bury (H)',
        'U15B' => 'Training',
        'U16B' => 'Training',
        'U18B' => 'Colts Cup',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '5-Apr-26',
        'Minis' => 'Easter',
        'U13B' => 'Easter',
        'U14B' => 'Easter',
        'U15B' => 'Easter',
        'U16B' => 'Easter',
        'U18B' => 'Easter',
        'U12G' => '',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '12-Apr-26',
        'Minis' => 'Training',
        'U13B' => 'Cambridge (A)',
        'U14B' => 'Cambridge (H)',
        'U15B' => 'Cambridge (A)',
        'U16B' => 'Cambridge (H)',
        'U18B' => 'Colts Cup Semis',
        'U12G' => '',
        'U14G' => 'Academy',
        'U16G' => 'Academy',
        'U18G' => ''
    ],
    [
        'date' => '19-Apr-26',
        'Minis' => 'NORWICH MINI FESTIVAL',
        'U13B' => 'EC Festival 3',
        'U14B' => 'Colchester Festival (A)',
        'U15B' => 'NORWICH MINI FESTIVAL',
        'U16B' => 'NORWICH MINI FESTIVAL',
        'U18B' => 'Colts Cup Finals',
        'U12G' => 'Conference Finals',
        'U14G' => 'Conference Finals',
        'U16G' => 'Conference Finals',
        'U18G' => 'Conference Finals'
    ],
    [
        'date' => '26-Apr-26',
        'Minis' => 'Training',
        'U13B' => 'Training',
        'U14B' => 'Training',
        'U15B' => 'Beccles (H)',
        'U16B' => 'Beccles (H)',
        'U18B' => 'Colts 7s',
        'U12G' => 'Love Rugby Festival      (U9-U12 non-contact)',
        'U14G' => '',
        'U16G' => '',
        'U18G' => ''
    ],
    [
        'date' => '3-May-26',
        'Minis' => 'OUT OF SEASON',
        'U13B' => 'OUT OF SEASON',
        'U14B' => 'Norfolk Finals / France tour',
        'U15B' => 'Norfolk Finals',
        'U16B' => 'Norfolk Finals',
        'U18B' => 'CB U17',
        'U12G' => '',
        'U14G' => 'Norfolk Finals',
        'U16G' => 'Norfolk Finals/Nat Cup',
        'U18G' => 'Norfolk Finals/Nat Cup'
    ]
];

}
