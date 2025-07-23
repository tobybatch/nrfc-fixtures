<?php

namespace App\Controller;

use App\Config\Competition;
use App\Config\HomeAway;
use App\Config\Team;
use App\DTO\ImportExportDTO;
use App\Entity\Club;
use App\Entity\Fixture;
use App\Form\CsvUploadType;
use App\Repository\ClubRepository;
use App\Repository\FixtureRepository;
use App\Service\ImportExportService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        ImportExportService    $importExportService,
        ClubRepository         $clubRepository,
    ): Response
    {
        // Instantiate forms
        $clubForm = $this->createForm(CsvUploadType::class, null, ['attr' => ['id' => 'club-form']]);
        $fixtureForm = $this->createForm(CsvUploadType::class, null, ['attr' => ['id' => 'fixture-form']]);

        $clubForm->handleRequest($request);
        $fixtureForm->handleRequest($request);

        $result = null;

        // Handle Club CSV
        if ($clubForm->isSubmitted() && $clubForm->isValid()) {
            $handle = fopen($clubForm->get('csv')->getData(), 'r');
            $result = $importExportService->readClubsFromCsvFile($handle);
            fclose($handle);
            $em->flush();
        }

        // Handle Fixture CSV
        if ($fixtureForm->isSubmitted() && $fixtureForm->isValid()) {
            $handle = fopen($clubForm->get('csv')->getData(), 'r');
            $result = $importExportService->readFixturesFromCsvFile($handle);
            fclose($handle);
            $em->flush();
        }

        return $this->render('admin/import_export.html.twig', [
            'club_form' => $clubForm->createView(),
            'fixture_form' => $fixtureForm->createView(),
            'result' => $result,
        ]);
    }

    #[Route('/clubs/initialise', name: 'admin_clubs_initialise')]
    public function initialiseClubs(ParameterBagInterface $bag, ImportExportService $importExportService, LoggerInterface $logger): Response
    {
        $pathFromPublicFolder = '../' . $bag->get('asset_path_clubs');
        $logger->info($pathFromPublicFolder);
        $handle = fopen($pathFromPublicFolder, 'r+');
        $result = $importExportService->readClubsFromCsvFile($handle);
        fclose($handle);

        // This should never error but just in case
        foreach ($result->getErrors() as $error) {
            $logger->error($error);
            $this->addFlash('danger', $error);
        }

        $this->addFlash('success', sprintf('%d Clubs imported successfully', $result->getSuccessCount()));
        return $this->redirectToRoute('app_club_index');
    }

    #[Route('/clubs/export', name: 'admin_clubs_export')]
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

    #[Route('/fixtures/export', name: 'admin_fixtures_export')]
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
