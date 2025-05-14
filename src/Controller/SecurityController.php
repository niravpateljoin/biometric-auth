<?php

namespace App\Controller;

use App\Entity\User;
use App\Helper\MailerHelper;
use App\Helper\TotpHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[IsGranted('PUBLIC_ACCESS')]
class SecurityController extends AbstractController
{
    public function __construct(private readonly TotpHelper $totpHelper, private readonly MailerHelper $mailerHelper)
    {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('dashboard');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/2fa', name: 'app_2fa', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function twoFactorAuth(): Response
    {
        return $this->render('security/2fa_verify.html.twig');
    }

    #[Route('/2fa/choices', name: 'app_2fa_choices', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function twoFactorAuthChoices(): Response
    {
        return $this->render('security/2fa_choices.html.twig');
    }

    #[Route('/2fa/verify', name: 'app_2fa_verify', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function twoFactorAuthVerify(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $totpCode = implode('', $request->get('code'));
        $isValid = $this->totpHelper->verifyOtp($user, $totpCode);

        if ($isValid) {
            $request->getSession()->set('2fa_verify', true);
            return $this->redirectToRoute('dashboard');
        } else {
            return $this->redirectToRoute('app_2fa');
        }
    }

    #[Route('/bio-metrics-auth', name: 'app_biometric_auth', methods: ['GET', 'POST'])]
    public function biometricsAuth(): Response
    {
        return $this->render('security/biometrics_auth.html.twig');
    }
}
