<?php

namespace App\DataFixtures;

use App\Entity\Club;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\HttpKernel\KernelInterface;

class Clubs extends Fixture
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $clubs = [
            ['name' => "Beccles", 'addr' => "Beef Meadow, Common Ln, Beccles NR34 9RH", 'lat' => 52.4591023, 'lon' => 1.5720259], // Beccles RUFC
            ['name' => "Bury", 'addr' => "The GK IPA Haberden, Southgate Grn, Bury Saint Edmunds IP33 2BL", 'lat' => 52.2293, 'lon' => 0.7085], // Bury St Edmunds RUFC
            ['name' => "Cambridge", 'addr' => "Fields, Ellgia, Grantchester Rd, Cambridge CB3 9ED", 'lat' => 52.1829, 'lon' => 0.0993], // Cambridge RUFC
            ['name' => "Colchester", 'addr' => "Raven Park, Cuckoo Farm Way, Colchester CO4 5YX", 'lat' => 51.9194, 'lon' => 0.9259], // Colchester RUFC
            ['name' => "Crusaders", 'addr' => "Beckhithe, Little Melton Rd, Hethersett, Norwich NR9 3NP", 'lat' => 52.6106217, 'lon' => 1.1813909], // Norwich Crusaders RFC
            ['name' => "Diss", 'addr' => "Bellrope Ln, Roydon, Diss IP22 5RG", 'lat' => 52.383274, 'lon' => 1.07904], // Diss RFC
            ['name' => "Fakenham", 'addr' => "Eckersley Dr, Fakenham NR21 9RZ", 'lat' => 52.8410931, 'lon' => 0.8412776], // Fakenham RFC
            ['name' => "Haverhill", 'addr' => "School Ln, Haverhill CB9 9DE", 'lat' => 52.084039, 'lon' => 0.4184581], // Haverhill & District RFC
            ['name' => "Holt", 'addr' => "Bridge Rd, Holt NR25 6QT", 'lat' => 52.9130816, 'lon' => 1.1081743], // Holt RFC
            ['name' => "Ipswich", 'addr' => "Humber Doucy Lane, Ipswich IP4 3PZ", 'lat' => 52.0715004, 'lon' => 1.1959668], // Ipswich RFC
            ['name' => "L&Y", 'addr' => "Old Ln, Corton, Lowestoft NR32 5HE", 'lat' => 52.5080705, 'lon' => 1.7361474], // Lowestoft & Yarmouth RFC
            ['name' => "Newmarket", 'addr' => "Pavilion, Scatlback, Elizabeth Ave, Newmarket CB8 0DJ", 'lat' => 52.2533999, 'lon' => 0.3880533], // Newmarket RUFC
            ['name' => "North Walsham", 'addr' => "Road, The Clubhouse, Scottow, Norwich NR10 5BU", 'lat' => 52.760951, 'lon' => 1.3682356], // North Walsham RFC
            ['name' => "Shelford", 'addr' => "The Davey Field, Cambridge Rd, Great Shelford, Cambridge CB22 5JJ", 'lat' => 52.1587446, 'lon' => 0.1240375], // Shelford RFC
            ['name' => "Southwold", 'addr' => "The Common, Southwold IP18 6TB", 'lat' => 52.3270181, 'lon' => 1.6681319], // Southwold RFC
            ['name' => "Stowmarket", 'addr' => "Chilton Fields, Chilton Way, Stowmarket IP14 1SZ", 'lat' => 52.1952855, 'lon' => 0.9723984], // Stowmarket RFC
            ['name' => "West Norfolk", 'addr' => "Gate House/Gate House La, King's Lynn PE30 3RJ", 'lat' => 52.7955611, 'lon' => 0.2816534], // West Norfolk RFC
            ['name' => "Wisbech", 'addr' => "Chapel Rd, Wisbech PE13 1RG", 'lat' => 52.6672446, 'lon' => 0.1562595], // Wisbech RUFC
            ['name' => "Woodbridge", 'addr' => "Hatchley Barn, Orford Rd, Bromeswell, Woodbridge IP12 2PP", 'lat' => 52.1029813, 'lon' => 1.3760001], // Woodbridge RFC
            ['name' => "Wymondham", 'addr' => "Barnard Fields, off Bray Dr, Reeve Way, Wymondham NR18 0GQ", 'lat' => 52.5900641, 'lon' => 1.1401406], // Wymondham RFC
        ];

        foreach ($clubs as $club) {
            $c = new Club();
            $c->setName($club["name"]);
            $c->setAddress($club["addr"]);
            $c->setLatitude($club["lat"]);
            $c->setLongitude($club["lon"]);
            $manager->persist($c);
        }
        $manager->flush();
    }
}
