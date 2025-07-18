<?php

namespace App\Controller;

use App\Config\Team;
use App\Form\ImportClubsType;
use App\Repository\ClubRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/data')]
final class DataController extends AbstractController
{
    private ClubRepository $clubRepository;

    public function __construct(ClubRepository $clubRepository)
    {
        $this->clubRepository = $clubRepository;
    }

    #[Route('/clubs/export', name: 'export_clubs')]
    public function export(): Response
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


    #[Route('/clubs/import', name: 'club_import')]
    public function import(
        Request $request,
        EntityManagerInterface $em,
        ClubRepository $clubRepository
    ): Response {
        $form = $this->createForm(ImportClubsType::class);
        $form->handleRequest($request);

        $message = null;
        $errors = [];
        $successCount = 0;

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csv')->getData();

            if ($file) {
                try {
                    $handle = fopen($file->getPathname(), 'r');
                    $header = fgetcsv($handle);
                    $rowNum = 1;

                    while (($data = fgetcsv($handle)) !== false) {
                        $rowNum++;
                        try {
                            $row = array_combine($header, $data);
                            if (!$row || !isset($row['Name'])) {
                                $errors[] = "Row $rowNum: Missing or invalid 'Name' field.";
                                continue;
                            }

                            $name = trim($row['Name']);
                            if ($name === '') {
                                $errors[] = "Row $rowNum: 'Name' cannot be empty.";
                                continue;
                            }

                            $club = $clubRepository->findOneByNameInsensitive($name) ?? new Club();
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
                                    $errors[] = "Row $rowNum: Invalid JSON in 'Aliases'.";
                                }
                            }

                            $em->persist($club);
                            $successCount++;
                        } catch (\Throwable $e) {
                            $errors[] = "Row $rowNum: Unexpected error - " . $e->getMessage();
                        }
                    }

                    fclose($handle);
                    $em->flush();
                    $message = "$successCount clubs processed successfully.";
                } catch (FileException|\Throwable $e) {
                    $errors[] = "Failed to read or process file: " . $e->getMessage();
                }
            }
        }

        return $this->render('club/import.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
            'errors' => $errors,
        ]);
    }

    #[Route('/export/fixtures', name: 'export_fixtures')]
    public function fixtures(): Response
    {
        return $this->render('data/index.html.twig', [
            'controller_name' => 'DataController',
        ]);
    }
}
