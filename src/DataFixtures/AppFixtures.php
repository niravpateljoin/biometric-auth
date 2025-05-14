<?php

namespace App\DataFixtures;

use App\Entity\Enum\UserRole;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
         $user = new User();
         $user->setEmail('superadmin@system.com');
         $password = $this->userPasswordHasher->hashPassword($user, 'Admin#@123');
         $user->setPassword($password);
         $user->setName('Super Admin System');
         $user->setEnabled(true);
         $user->setRole(UserRole::SUPER_ADMIN);
         $user->setEnable2fa(false);
         $user->setEnableBioMetricsFor2fa(false);

         $manager->persist($user);

         $manager->flush();
    }
}
