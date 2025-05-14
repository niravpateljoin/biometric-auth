<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\BioMetricData;
use App\Entity\User;
use App\Helper\BioMetricsHelper;
use App\Repository\BioMetricDataRepository;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[Route('/bio-metrics')]
#[IsGranted('ROLE_USER')]
class BioMetricsController extends AbstractController
{
    public function __construct(private readonly BioMetricsHelper $bioMetricsHelper, private readonly BioMetricDataRepository $bioMetricDataRepository)
    {
    }

    #[Route('/create-args', name: 'bio_metrics_create_args')]
    public function index(): JsonResponse
    {
        /** @var User $currentUser*/
        $currentUser = $this->getUser();
        try {
            $createdArgs = $this->bioMetricsHelper->createArgsAndStoreChallengeIntoSession((string) $currentUser->getId(), $currentUser->getEmail(), $currentUser->getName(), 30);
        } catch (Throwable $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'status' => true,
            'createdArgs' => $createdArgs,
        ]);
    }

    #[Route('/get-args', name: 'bio_metrics_get_args')]
    public function getArgs(): JsonResponse
    {
        /** @var User $currentUser*/
        $currentUser = $this->getUser();

        try {
            $getArgs = $this->bioMetricsHelper->getArgsForUser($currentUser);
        } catch (Throwable $e) {
            return $this->json([
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
        return $this->json([
            'success' => true,
            'status' => 'ok',
            'getArgs' => $getArgs,
        ]);
    }

    #[Route('/process-create', name: 'bio_metrics_process_create')]
    public function processGetRequest(Request $request): JsonResponse
    {
        $success = false;
        $errorMessage = null;

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        try {
            $bodyData = json_decode($request->getContent(), true);

            if (!isset($bodyData['clientDataJSON'], $bodyData['attestationObject'])) {
                throw new InvalidArgumentException("Missing WebAuthn credential data.");
            }

            $clientDataJSON = base64_decode($bodyData['clientDataJSON']);
            $attestationObject = base64_decode($bodyData['attestationObject']);

            $this->bioMetricsHelper->processCreateRequest($clientDataJSON, $attestationObject, $currentUser);
            $success = true;
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
        }

        return $this->json([
            'success' => $success,
            'errorMessage' => $errorMessage,
        ]);
    }

    #[Route('/check-bio-metric-registration', name: 'app_biometrics_check_biometric_registration')]
    public function checkBioMetricRegistration(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        try {
            $bodyData = json_decode($request->getContent(), true);

            if (!isset($bodyData['clientDataJSON']) || !isset($bodyData['authenticatorData']) || !isset($bodyData['signature']) || !isset($bodyData['id'])) {
                throw new InvalidArgumentException("Missing WebAuthn credential data.");
            }
            $clientDataJSON = base64_decode($bodyData['clientDataJSON']);
            $authenticatorData = base64_decode($bodyData['authenticatorData']);
            $signature = base64_decode($bodyData['signature']);
            $id = base64_decode($bodyData['id']);

            $biometricData = $this->bioMetricDataRepository->findOneBy([
                'user' => $currentUser,
                'credentialId' => bin2hex($id),
            ]);

            if (!$biometricData instanceof BioMetricData) {
                throw new InvalidArgumentException("Biometric data not found.");
            }

            $data = $biometricData->getData();
            if (!$data) {
                throw new InvalidArgumentException("Biometric data not found.");
            }

            $data = unserialize($data);
            $credentialPublicKey = $data->credentialPublicKey;

            if (!$credentialPublicKey) {
                throw new InvalidArgumentException("Biometric data not found.");
            }

            $this->bioMetricsHelper->processGetRequest($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $biometricData);

            $request->getSession()->set('2fa_verify', true);
            return $this->json([
                'status' => 'ok',
                'message' => 'Biometric data verified successfully',
            ]);
        } catch (Throwable $e) {
            $response = [
                'status' => 'error',
                'message' => 'Failed to retrieve bio metric data' . $e->getMessage(),
            ];
        }

        return $this->json($response);
    }
}
