<?php

namespace App\Repository;

use App\Entity\QuestionEvaluation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuestionEvaluation>
 *
 * @method QuestionEvaluation|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuestionEvaluation|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuestionEvaluation[]    findAll()
* @method QuestionEvaluation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
*/
class QuestionEvaluationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuestionEvaluation::class);
    }
    public function calculateQuizScore(array $submittedAnswers, array $correctAnswers): float
    {
        // Initialize score
        $score = 0;

        // Iterate over each submitted answer
        foreach ($submittedAnswers as $questionId => $submittedAnswer) {
            // Check if the submitted answer matches the correct answer
            if (isset($correctAnswers[$questionId]) && $submittedAnswer === $correctAnswers[$questionId]) {
                // Increment score if the answer is correct
                $score += 1.0; // Ensure a float value
            }
        }

        return $score;
    }

//    /**
//     * @return Quiz[] Returns an array of QuestionEvaluation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?QuestionEvaluation
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
