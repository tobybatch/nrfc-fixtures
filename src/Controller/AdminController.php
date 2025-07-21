<?php

namespace App\Controller;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Form\CsvUploadType;
use App\Repository\ClubRepository;
use App\Repository\FixtureRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;


#[Route('/admin')]
final class AdminController extends AbstractController
{
    private ClubRepository $clubRepository;

    public function __construct(ClubRepository $clubRepository)
    {
        $this->clubRepository = $clubRepository;
    }

    #[Route('/', name: 'admin_index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/importExport', name: 'admin_import_export')]
    public function importExport(
        Request                $request,
        EntityManagerInterface $em,
        ClubRepository         $clubRepository
    ): Response
    {
        // Instantiate forms
        $clubForm = $this->createForm(CsvUploadType::class, null, ['attr' => ['id' => 'club-form']]);
        $fixtureForm = $this->createForm(CsvUploadType::class, null, ['attr' => ['id' => 'fixture-form']]);

        $clubForm->handleRequest($request);
        $fixtureForm->handleRequest($request);

        $messages = [];
        $errors = [];

        // Handle Club CSV
        if ($clubForm->isSubmitted() && $clubForm->isValid()) {
            $result = $this->handleClubCsv($clubForm->get('csv')->getData(), $em, $clubRepository);
            $messages[] = $result['message'];
            $errors = array_merge($errors, $result['errors']);
        }

        // Handle Fixture CSV
        if ($fixtureForm->isSubmitted() && $fixtureForm->isValid()) {
            $result = $this->handleFixtureCsv($fixtureForm->get('csv')->getData(), $em, $clubRepository);
            $messages[] = $result['message'];
            $errors = array_merge($errors, $result['errors']);
        }

        return $this->render('admin/import_export.html.twig', [
            'club_form' => $clubForm->createView(),
            'fixture_form' => $fixtureForm->createView(),
            'messages' => $messages,
            'errors' => $errors,
        ]);
    }

    private function handleClubCsv($file, EntityManagerInterface $em, ClubRepository $clubRepo): array
    {
        $errors = [];
        $successCount = 0;

        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);
        $rowNum = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            try {
                $row = array_combine($header, $data);
                $name = trim($row['Name'] ?? '');
                if ($name === '') {
                    $errors[] = "Club row $rowNum: Missing name.";
                    continue;
                }

                $club = $clubRepo->findOneByNameInsensitive($name) ?? new Club();
                $club->setName($name);
                $club->setAddress($row['Address'] ?? null);
                $club->setLatitude(isset($row['Latitude']) ? (float)$row['Latitude'] : null);
                $club->setLongitude(isset($row['Longitude']) ? (float)$row['Longitude'] : null);
                $club->setNotes($row['Notes'] ?? null);

                if (!empty($row['Aliases'])) {
                    $aliases = json_decode($row['Aliases'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($aliases)) {
                        $club->setAliases($aliases);
                    } else {
                        $errors[] = "Club row $rowNum: Invalid JSON in 'Aliases'.";
                    }
                }

                $em->persist($club);
                $successCount++;
            } catch (\Throwable $e) {
                $errors[] = "Club row $rowNum: " . $e->getMessage();
            }
        }

        fclose($handle);
        $em->flush();

        return [
            'message' => "$successCount clubs processed.",
            'errors' => $errors,
        ];
    }

    private function handleFixtureCsv($file, EntityManagerInterface $em, ClubRepository $clubRepo): array
    {
        $errors = [];
        $successCount = 0;

        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);
        $rowNum = 1;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            try {
                $row = array_combine($header, $data);

                $club = $clubRepo->findOneByNameInsensitive(trim($row['Club'] ?? ''));
                if (!$club) {
                    $errors[] = "Fixture row $rowNum: Club '{$row['Club']}' not found.";
                    continue;
                }

                $name = new \DateTimeImmutable($row['Name'] ?? '');
                $date = new \DateTimeImmutable($row['Date'] ?? 'now');
                $homeAway = HomeAway::tryFrom($row['HomeAway'] ?? '');
                $competition = Competition::tryFrom($row['Competition'] ?? '');
                $team = Team::tryFrom($row['Team'] ?? '');
                $opponent = isset($row['Opponent']) ? Team::tryFrom($row['Opponent']) : null;

                if (!$homeAway || !$competition || !$team) {
                    $errors[] = "Fixture row $rowNum: Invalid enum value.";
                    continue;
                }

                $fixture = new Fixture();
                $fixture->setName($name);
                $fixture->setDate($date);
                $fixture->setClub($club);
                $fixture->setHomeAway($homeAway);
                $fixture->setCompetition($competition);
                $fixture->setTeam($team);
                $fixture->setOpponent($opponent);
                $fixture->setName($row['Name'] ?? null);
                $fixture->setNotes($row['Notes'] ?? null);

                $em->persist($fixture);
                $successCount++;
            } catch (\Throwable $e) {
                $errors[] = "Fixture row $rowNum: " . $e->getMessage();
            }
        }

        fclose($handle);
        $em->flush();

        return [
            'message' => "$successCount fixtures imported.",
            'errors' => $errors,
        ];
    }

    #[Route('/clubs/export', name: 'admin_export_clubs')]
    public function exportClubs(): Response
    {
        $clubs = $this->clubRepository->findAll();

        $handle = fopen('php://temp', 'r+');

        // CSV header
        fputcsv($handle, ['ID', 'Name', 'Address', 'Latitude', 'Longitude', 'Notes', 'Aliases']);

        foreach ($clubs as $club) {
            fputcsv($handle, [
                $club->getId(),
                $club->getName(),
                $club->getAddress(),
                $club->getLatitude(),
                $club->getLongitude(),
                $club->getNotes(),
                $club->getAliases() ? json_encode($club->getAliases()) : '',
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return new Response(
            $csvContent,
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="clubs.csv"',
            ]
        );
    }

    #[Route('/fixtures/export', name: 'admin_export_fixtures')]
    public function fixtures(FixtureRepository $fixtureRepository): Response
    {
        $fixtures = $fixtureRepository->findAll();

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Name', 'Date', 'Club', 'HomeAway', 'Competition', 'Team', 'Name', 'Notes', 'Opponent']);

        foreach ($fixtures as $fixture) {
            fputcsv($handle, [
                $fixture->getName(),
                $fixture->getDate()?->format('Y-m-d'),
                $fixture->getClub()?->getName(),
                $fixture->getHomeAway()->name,
                $fixture->getCompetition()->name,
                $fixture->getTeam()->name,
                $fixture->getName(),
                $fixture->getNotes(),
                $fixture->getOpponent()?->name,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="fixtures.csv"',
        ]);
    }


}
