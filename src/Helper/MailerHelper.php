<?php

namespace App\Helper;

use App\Entity\User;
use RuntimeException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Throwable;

readonly class MailerHelper
{
    public function __construct(private MailerInterface $mailer, private TotpHelper $totpHelper)
    {
    }

    public function sendTestMail(User $user): void
    {
        $this->sendMail('Test Mail', $user->getEmail(), 'emails/test_email.html.twig', ['user' => $user]);;
    }

    public function sendTotpMail(User $user):  void
    {
        $totpCode = $this->totpHelper->generateOtp($user);
        $this->sendMail('2FA Code', $user->getEmail(), 'emails/totp_email.html.twig', ['user' => $user, 'otp' => $totpCode, 'validForMinutes' => 10]);;
    }

    private function sendMail(string $subject, string $to, string $emailTemplate, array $parameters = []): void
    {
        $mail = new TemplatedEmail()
            ->from('systemadmin@biometricapp.com')
            ->addTo($to)
            ->subject($subject)
            ->htmlTemplate($emailTemplate)
            ->context($parameters);
        try {
            $this->mailer->send($mail);

        } catch (Throwable $e) {
            throw new RuntimeException('Failed to send e-mail, '.$e->getMessage());
        }
    }
}
