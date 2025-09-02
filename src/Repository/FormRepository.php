<?php

namespace App\Repository;

use App\Entity\Form;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Form|null find($id, $lockMode = null, $lockVersion = null)
 * @method Form|null findOneBy(array $criteria, array $orderBy = null)
 * @method Form[]    findAll()
 * @method Form[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FormRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Form::class);
    }

    /**
     * Save a Form entity.
     *
     * @param Form $form
     */
    public function save(Form $form): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($form);
        $entityManager->flush();
    }

    /**
     * Update a Form entity.
     *
     * @param Form $form
     */
    public function update(Form $form): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->flush();
    }

    /**
     * Delete a Form entity.
     *
     * @param Form $form
     */
    public function delete(Form $form): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($form);
        $entityManager->flush();
    }

    /**
     * Find a Form entity by its ID.
     *
     * @param int $id
     * @return Form|null
     */
    public function findById(int $id): ?Form
    {
        return $this->find($id);
    }
    public function findAllForms(): array
    {
        return $this->createQueryBuilder('f')
            ->getQuery()
            ->getResult();
    }
    
}
