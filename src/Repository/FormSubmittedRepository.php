<?php

namespace App\Repository;

use App\Entity\Formsubmitted;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Formsubmitted|null find($id, $lockMode = null, $lockVersion = null)
 * @method Formsubmitted|null findOneBy(array $criteria, array $orderBy = null)
 * @method Formsubmitted[]    findAll()
 * @method Formsubmitted[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormSubmittedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Formsubmitted::class);
    }

    /**
     * Save a Formsubmitted entity.
     *
     * @param Formsubmitted $formSubmitted
     */
    public function save(Formsubmitted $formSubmitted): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($formSubmitted);
        $entityManager->flush();
    }

    /**
     * Update a Formsubmitted entity.
     *
     * @param Formsubmitted $formSubmitted
     */
    public function update(Formsubmitted $formSubmitted): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->flush();
    }

    /**
     * Delete a Formsubmitted entity.
     *
     * @param Formsubmitted $formSubmitted
     */
    public function delete(Formsubmitted $formSubmitted): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($formSubmitted);
        $entityManager->flush();
    }

    /**
     * Find a Formsubmitted entity by its ID.
     *
     * @param int $id
     * @return Formsubmitted|null
     */
    public function findById(int $id): ?Formsubmitted
    {
        return $this->find($id);
    }
}
