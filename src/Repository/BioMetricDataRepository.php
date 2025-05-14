<?php

namespace App\Repository;

use App\Entity\BioMetricData;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BioMetricData>
 */
class BioMetricDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BioMetricData::class);
    }

    /**
     * @return string[]
     */
    public function getCredentialsForUser(User $user): array
    {
        return $this->createQueryBuilder('wr')
            ->select('wr.credentialId')
            ->where('wr.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
