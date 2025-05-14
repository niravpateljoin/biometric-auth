<?php

namespace App\Entity\Enum;

use Elao\Enum\Attribute\EnumCase;
use Elao\Enum\ExtrasTrait;
use Elao\Enum\ReadableEnumInterface;
use Elao\Enum\ReadableEnumTrait;

enum UserRole: string implements ReadableEnumInterface
{
    use ReadableEnumTrait;
    use ExtrasTrait;

    #[EnumCase('Super Admin', extras: ['badge_class' => 'bg-primary'])]
    case SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    #[EnumCase('Admin', extras: ['badge_class' => 'bg-success'])]
    case ADMIN = 'ROLE_ADMIN';

    #[EnumCase('User', extras: ['badge_class' => 'bg-info'])]
    case USER = 'ROLE_USER';

    public function getBadgeClass(): string
    {
        return $this->getExtra('badge_class', true);
    }
}
