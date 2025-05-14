<?php

namespace App\Security\EventSubscriber;

use App\Controller\SecurityController;
use App\Entity\User;
use App\Helper\MailerHelper;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\SecurityBundle\Security\FirewallConfig;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class UserAuthenticationSubscriber implements EventSubscriberInterface
{
    private const array FIREWALLS = ['main'];

    public function __construct(
        #[Autowire('@security.user_checker_locator')]
        private ServiceLocator $serviceLocator,
        private readonly Security $security,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly MailerHelper $mailerHelper,
        private RedirectController $redirectController,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            KernelEvents::REQUEST => 'onKernelRequest',
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        $request = $event->getRequest();

        if (!$user instanceof User) {
            return;
        }

        $session = $request->getSession();
        $session->set('2fa_verify', false);

        if ($user->isEnable2fa() && $user->isEnableBioMetricsFor2fa()) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('app_2fa_choices')
            ));
        } elseif ($user->isEnable2fa()) {
            if (!$session->get('2fa_code_sent')) {
                $this->mailerHelper->sendTotpMail($user);
                $session->set('2fa_code_sent', true);
            }

            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('app_2fa')
            ));
        } elseif ($user->isEnableBioMetricsFor2fa()) {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('app_biometric_auth')
            ));
        } else {
            $event->setResponse(new RedirectResponse(
                $this->urlGenerator->generate('dashboard')
            ));
        }

        $event->stopPropagation();
    }


    public function onKernelRequest(RequestEvent $event): void
    {
        $firewallConfig = $this->security->getFirewallConfig($event->getRequest());
        $firewallName = $firewallConfig?->getName();

        if (!in_array($firewallName, self::FIREWALLS)) {
            return;
        }

        $currentUser = $this->tokenStorage->getToken()?->getUser();

        if ($currentUser instanceof User && $firewallConfig instanceof FirewallConfig) {
            try {
                $serviceArr = explode('.', $firewallConfig->getUserChecker());
                $serviceName = end($serviceArr);
                /** @var UserCheckerInterface $userCheckerService*/
                $userCheckerService = $this->serviceLocator->get($serviceName);
                $userCheckerService->checkPreAuth($currentUser);
                $userCheckerService->checkPostAuth($currentUser);
            } catch (AuthenticationException $e) {
                /** @var Response $logoutResponse */
                $logoutResponse = $this->security->logout(false);
                $event->setResponse($logoutResponse);
                $event->stopPropagation();
                return;
            }
        }
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $currentUser = $this->tokenStorage->getToken()?->getUser();
        $request = $event->getRequest();

        if (!$currentUser instanceof User) {
            return;
        }

        $twoFactorCodeSent = $request->getSession()->get('2fa_code_sent');
        $twoFactorVerification = $request->getSession()->get('2fa_verify');

        if ($currentUser->isEnableBioMetricsFor2fa() || $currentUser->isEnable2fa()) {
            if (!in_array($request->attributes->get('_route'),
                [
                    'app_2fa_choices',
                    'app_2fa',
                    'app_2fa_verify',
                    'app_biometric_auth',
                    'bio_metrics_get_args',
                    'app_biometrics_check_biometric_registration',
                ], true))
            {
                if (!$twoFactorVerification) {
                    if ($currentUser->isEnableBioMetricsFor2fa() && $currentUser->isEnable2fa()) {
                        $response = $this->redirectController->redirectAction($request, 'app_2fa_choices');
                        $event->setController(fn () => $response);
                    } elseif ($currentUser->isEnable2fa()) {
                        $response = $this->redirectController->redirectAction($request, 'app_2fa');
                        $event->setController(fn () => $response);
                    } else {
                        $response = $this->redirectController->redirectAction($request, 'app_biometric_auth');
                        $event->setController(fn () => $response);
                    }
                }
            } else {
                if ($currentUser->isEnable2fa() && !$twoFactorCodeSent && $request->attributes->get('_route') === 'app_2fa') {
                    $this->mailerHelper->sendTotpMail($currentUser);
                    $request->getSession()->set('2fa_code_sent', true);
                }
                if ($twoFactorVerification) {
                    $response = $this->redirectController->redirectAction($request, 'dashboard');
                    $event->setController(fn () => $response);
                }
            }
        }
    }
}
