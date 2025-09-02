<?php

namespace App\Repository;

use App\Entity\Infraction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InfractionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Infraction::class);    
    }

    public function findInfractionById(int $InfractionId): ?Infraction
    {
        return $this->find($InfractionId);
    }


}    
