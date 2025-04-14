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
            [ 'name'  => "Beccles", 'addr' =>"Beef Meadow, Common Ln, Beccles NR34 9RH" ],
            [ 'name'  => "Bury", 'addr' =>"The GK IPA Haberden, Southgate Grn, Bury Saint Edmunds IP33 2BL" ],
            [ 'name'  => "Cambridge", 'addr' =>"Fields, Ellgia, Grantchester Rd, Cambridge CB3 9ED" ],
            [ 'name'  => "Colchester", 'addr' =>"Raven Park, Cuckoo Farm Way, Colchester CO4 5YX" ],
            [ 'name'  => "Crusaders", 'addr' =>"Beckhithe, Little Melton Rd, Hethersett, Norwich NR9 3NP" ],
            [ 'name'  => "Diss", 'addr' =>"Bellrope Ln, Roydon, Diss IP22 5RG" ],
            [ 'name'  => "Fakenham", 'addr' =>"Eckersley Dr, Fakenham NR21 9RZ" ],
            [ 'name'  => "Haverhill", 'addr' =>"School Ln, Haverhill CB9 9DE" ],
            [ 'name'  => "Holt", 'addr' =>"Bridge Rd, Holt NR25 6QT" ],
            [ 'name'  => "Ipswich", 'addr' =>"Humber Doucy Lane, Ipswich IP4 3PZ" ],
            [ 'name'  => "L&Y", 'addr' =>"Old Ln, Corton, Lowestoft NR32 5HE" ],
            [ 'name'  => "Newmarket", 'addr' =>"Pavilion, Scatlback, Elizabeth Ave, Newmarket CB8 0DJ" ],
            [ 'name'  => "North Walsham", 'addr' =>"Road, The Clubhouse, Scottow, Norwich NR10 5BU" ],
            [ 'name'  => "Shelford", 'addr' =>"The Davey Field, Cambridge Rd, Great Shelford, Cambridge CB22 5JJ" ],
            [ 'name'  => "Southwold", 'addr' =>"The Common, Southwold IP18 6TB" ],
            [ 'name'  => "Stowmarket", 'addr' =>"Chilton Fields, Chilton Way, Stowmarket IP14 1SZ" ],
            [ 'name'  => "West Norfolk", 'addr' =>"Gate House/Gate House La, King's Lynn PE30 3RJ" ],
            [ 'name'  => "Wisbech", 'addr' =>"Chapel Rd, Wisbech PE13 1RG" ],
            [ 'name'  => "Woodbridge", 'addr' =>"Hatchley Barn, Orford Rd, Bromeswell, Woodbridge IP12 2PP" ],
            [ 'name'  => "Wymondham", 'addr' =>"Barnard Fields, off Bray Dr, Reeve Way, Wymondham NR18 0GQ" ],
        ];

        foreach ($clubs as $club) {
            $c = new Club();
            $c->setName($club["name"]);
            $c->setAddress($club["addr"]);
            $manager->persist($c);
        }
        $manager->flush();
    }
}
