<?php

namespace App\Repository;

use App\Entity\Answer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Answer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Answer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Answer[]    findAll()
 * @method Answer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Answer::class);
    }

    /**
     * Save an Answer entity.
     *
     * @param Answer $answer
     */
    public function save(Answer $answer): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($answer);
        $entityManager->flush();
    }

    /**
     * Update an Answer entity.
     *
     * @param Answer $answer
     */
    public function update(Answer $answer): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->flush();
    }

    /**
     * Delete an Answer entity.
     *
     * @param Answer $answer
     */
    public function delete(Answer $answer): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($answer);
        $entityManager->flush();
    }

    /**
     * Find an Answer entity by its ID.
     *
     * @param int $id
     * @return Answer|null
     */
    public function findById(int $id): ?Answer
    {
        return $this->find($id);
    }
}
