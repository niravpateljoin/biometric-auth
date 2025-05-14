<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct($registry, User::class);
    }

    public function saveUser(User $entity, ?string $plainPassword = null): void
    {
        if (null !== $plainPassword) {
            $entity->setPassword($this->userPasswordHasher->hashPassword($entity, $plainPassword));
        }
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }

    public function remove(User $entity): void
    {
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }
}
