<?php

namespace App\Repository;

use App\Entity\BadWords;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BadWordsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BadWords::class); // Fixed the entity class name
    }

    public function findBadWordById(int $badWordId): ?BadWords // Corrected method name and parameter name
    {
        return $this->find($badWordId);
    }
}
