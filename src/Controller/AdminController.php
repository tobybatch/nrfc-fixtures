<?php

namespace App\Controller;

use App\Form\CsvUploadType;
use App\Repository\ClubRepository;
use App\Repository\FixtureRepository;
use App\Service\ImportExportService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/admin')]
final class AdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly KernelInterface        $kernel,
        private readonly FixtureRepository $fixtureRepository,
        private readonly ClubRepository         $clubRepository,
        private readonly ParameterBagInterface  $bag,
        private readonly ImportExportService    $importExportService,
        private readonly LoggerInterface        $logger
    ){}

    #[Route('/', name: 'admin_index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/importExport', name: 'admin_import_export')]
    public function importExport(Request                $request): Response
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
            $result = $this->importExportService->readClubsFromCsvResource($handle);
            fclose($handle);
            $this->em->flush();
            if ($result->getErrors()) {
                foreach ($result->getErrors() as $error) {
                    $this->addFlash('danger', $error);
                }
            }
            if ($result->getSuccessCount()) {
                $this->addFlash('success', sprintf('%d Clubs imported successfully', $result->getSuccessCount()));
            }
            if ($result->getUpdateCount()) {
                $this->addFlash('success', sprintf('%d Clubs updated successfully', $result->getUpdateCount()));
            }
            return $this->redirectToRoute('admin_import_export');
        }

        // Handle Fixture CSV
        if ($fixtureForm->isSubmitted() && $fixtureForm->isValid()) {
            $handle = fopen($clubForm->get('csv')->getData(), 'r');
            $result = $this->importExportService->readFixturesFromCsvResource($handle);
            fclose($handle);
            $this->em->flush();
        }

        return $this->render('admin/import_export.html.twig', [
            'club_form' => $clubForm->createView(),
            'fixture_form' => $fixtureForm->createView(),
            'result' => $result,
        ]);
    }

    #[Route('/clubs/initialise', name: 'admin_clubs_initialise')]
    public function initialiseClubs(): Response
    {
        $clubsSrc = $this->kernel->getProjectDir() . '/' . $this->bag->get('asset_path_clubs');
        $handle = fopen($clubsSrc, 'r+');
        $result = $this->importExportService->readClubsFromCsvResource($handle);
        fclose($handle);

        // This should never error but just in case
        foreach ($result->getErrors() as $error) {
            $this->logger->error($error);
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
        $this->importExportService->writeClubsToCsvResource($handle, $clubs);
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
    public function fixtures(): Response
    {
        $fixtures = $this->fixtureRepository->findAll();

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
