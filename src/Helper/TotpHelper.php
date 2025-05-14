<?php

namespace App\Helper;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class TotpHelper
{
    public function __construct(private ParameterBagInterface $parameterBag)
    {
    }

    public function generateOtp(User $user): string
    {
        $key = $this->parameterBag->get('OTP_MASTER_KEY');
        $window = floor(time() / 600);
        $data = $user->getEmail() . '|' . $window;

        $hash = hash_hmac('sha1', $data, $key);
        $code = hexdec(substr($hash, -6)) % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    public function verifyOtp(User $user, string $submittedOtp): bool
    {
        $key = $this->parameterBag->get('OTP_MASTER_KEY');
        $currentWindow = floor(time() / 600);

        for ($i = -1; $i <= 1; $i++) {
            $window = $currentWindow + $i;
            $data = $user->getEmail() . '|' . $window;
            $hash = hash_hmac('sha1', $data, $key);
            $expected = str_pad((string)(hexdec(substr($hash, -6)) % 1000000), 6, '0', STR_PAD_LEFT);

            if (hash_equals($expected, $submittedOtp)) {
                return true;
            }
        }

        return false;
    }

}
