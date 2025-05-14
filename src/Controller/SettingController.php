<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class SettingController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[Route('/settings', name: 'settings_index')]
    public function index(): Response
    {
        return $this->render('setting/index.html.twig');
    }

    #[Route('/settings/manage-two-factor-auth', name: 'settings_manage_two_factor_auth')]
    public function manageTwoFactorAuth(Request $request): JsonResponse
    {
        $csrfToken = $request->request->getString('_token');
        $status = false;
        $errorMessage = null;
        $enabled = $request->request->getBoolean('2fa');

        if (!$this->isCsrfTokenValid('manage_two_factor_auth', $csrfToken)) {
            $errorMessage = 'Invalid CSRF token';
        } else {
            try {
                /** @var User $user */
                $user = $this->getUser();
                $user->setEnable2fa($enabled);
                $this->userRepository->saveUser($user);
                $status = true;
            } catch (\Throwable $e) {
                $errorMessage = 'Failed to manage two factor auth, ' . $e->getMessage();
            }
        }

        return $this->json([
            'status' => $status,
            'errorMessage' => $errorMessage
        ]);
    }

    #[Route('/settings/manage-bio-metrics', name: 'settings_manage_bio_metrics')]
    public function manageBioMetrics(Request $request): JsonResponse
    {
        $csrfToken = $request->request->getString('_token');
        $status = false;
        $errorMessage = null;
        $enabled = $request->request->getBoolean('bio_metrics');

        if (!$this->isCsrfTokenValid('bio_metrics_auth', $csrfToken)) {
            $errorMessage = 'Invalid CSRF token';
        } else {
            try {
                /** @var User $user */
                $user = $this->getUser();
                $user->setEnableBioMetricsFor2fa($enabled);
                $this->userRepository->saveUser($user);
                $status = true;
            } catch (\Throwable $e) {
                $errorMessage = 'Failed to manage two factor auth, ' . $e->getMessage();
            }
        }

        return $this->json([
            'status' => $status,
            'errorMessage' => $errorMessage
        ]);
    }
}
