<?php

declare(strict_types=1);

namespace App\Twig\Extensions;

use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('enum', [EnumRuntime::class, 'createProxy']),
        ];
    }
}
