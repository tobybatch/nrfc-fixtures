<?php

namespace App\Controller;

use App\Form\FixturesDisplayOptionsForm;
use App\Form\Model\FixturesDisplayOptionsDTO;
use App\Service\PreferencesService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/preferences')]
final class PreferencesController extends AbstractController
{
    private PreferencesService $preferencesService;
    private LoggerInterface $logger;

    public function __construct(
        PreferencesService $preferencesService,
        LoggerInterface $logger,
    ) {
        $this->preferencesService = $preferencesService;
        $this->logger = $logger;
    }

    #[Route('/update', name: 'app_preferences_update')]
    public function update(Request $request): RedirectResponse
    {
        $this->logger->debug('Preferences', ['preferences' => $request->request->all()]);
        $displayOptions = new FixturesDisplayOptionsDTO();
        $teamsForm = $this->createForm(FixturesDisplayOptionsForm::class, $displayOptions,  [
            'csrf_protection' => false,
        ]);
        $teamsForm->handleRequest($request);
        if ($teamsForm->isSubmitted() && $teamsForm->isValid()) {
            $this->logger->warning('is valid');
            $this->preferencesService->setPreferences('teamsSelected', $displayOptions->teams);
            $this->preferencesService->setPreferences('showPastDates', $displayOptions->showPastDates);
        }

        $next = $request->query->get('team');
        if (null !== $next) {
            $this->logger->debug('Next set', ['next' => $next]);

            return new RedirectResponse($next);
        }
        $this->logger->debug('Next not set');

        return $this->redirectToRoute('app_fixture_index');
    }
}
