<?php

declare(strict_types=1);

namespace App\Helper;

use App\Entity\User;
use App\Entity\BioMetricData;
use App\Repository\BioMetricDataRepository;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;
use RuntimeException;
use stdClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

readonly class BioMetricsHelper
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $entityManager,
        private BioMetricDataRepository $bioMetricDataRepository,
    ) {
    }

    /**
     * @throws WebAuthnException
     */
    public function createArgsAndStoreChallengeIntoSession(
        string $userId,
        string $userIdentifier,
        string $userDisplayName,
        int $timout,
    ): StdClass {
        $webauthn = $this->getWebAuthn();
        $createdArgs = $webauthn->getCreateArgs($userId, $userIdentifier, $userDisplayName, $timout);
        $this->requestStack->getSession()->set('webauthn_challenge', $webauthn->getChallenge());

        return $createdArgs;
    }

    public function processCreateRequest(
        string $clientDataJSON,
        string $attestationObject,
        User $user,
    ): void {
        try {
            $webAuthn = $this->getWebAuthn();
            $challenge = $this->requestStack->getSession()->get('webauthn_challenge');

            if (!$challenge) {
                throw new InvalidArgumentException("Challenge not found in session.");
            }

            $data = $webAuthn->processCreate($clientDataJSON, $attestationObject, $challenge);

            $bioMetricsData = new BioMetricData();
            $bioMetricsData->setUser($user);
            $bioMetricsData->setLastUsedTime(CarbonImmutable::now());
            $bioMetricsData->setCreatedTime(CarbonImmutable::now());
            $bioMetricsData->setData(serialize($data));
            $bioMetricsData->setCredentialId(bin2hex($data->credentialId));

            $this->entityManager->persist($bioMetricsData);
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException('Failed process create request, ' . $e->getMessage());
        }
    }

    public function getArgsForUser(User $currentUser): StdClass
    {
        try {
            $userCredentials = $this->bioMetricDataRepository->getCredentialsForUser($currentUser);
            $ids = [];
            foreach ($userCredentials as $userCredential) {
                $ids[] = hex2bin($userCredential);
            }
            if (empty($ids)) {
                throw new RuntimeException('no registrations in session.');
            }
            $webAuthn = $this->getWebAuthn();
            $getArgs = $webAuthn->getGetArgs($ids);
            $this->requestStack->getSession()->set('webauthn_challenge', $webAuthn->getChallenge());
            return $getArgs;
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    public function processGetRequest(
        string $clientDataJSON,
        string $authenticatorData,
        string $signature,
        string $credentialPublicKey,
        BioMetricData $bioMetricData,
    ): void {
        try {
            $challenge = $this->requestStack->getSession()->get('webauthn_challenge');

            if (!$challenge) {
                throw new InvalidArgumentException("Challenge not found in session.");
            }

            $webAuthn = $this->getWebAuthn();
            $webAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge);
            $bioMetricData->setLastUsedTime(CarbonImmutable::now());
            $this->entityManager->persist($bioMetricData);
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new RuntimeException('Failed process get request, ' . $e->getMessage());
        }
    }


    /**
     * @throws WebAuthnException
     */
    private function getWebAuthn(): WebAuthn
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new RuntimeException('Invalid Request');
        }

            $rpId = 'localhost';
        $formats = ['android-key', 'android-safetynet', 'fido-u2f', 'none', 'packed'];
        return new WebAuthn('My Application', $rpId, $formats);
    }
}
