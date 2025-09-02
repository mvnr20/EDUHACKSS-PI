<?php

namespace App\Repository;

use App\Entity\QuizResult;
use App\Entity\Quiz;
use App\Repository\QuizRepository ;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class QuizResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuizResult::class);
    }
    public function getQuizStatistics(): array
    {
        $entityManager = $this->getEntityManager();
        
        // Fetch all quizzes
        $quizzes = $entityManager->getRepository(Quiz::class)->findAll();
        
        // Initialize total counts
        $totalSubmittedCount = 0;
        $totalFailedCount = 0;
        $totalSuccessfulCount = 0;
    
        foreach ($quizzes as $quiz) {
            // Fetch the submitted count for the current quiz
            $submittedCount = $this->createQueryBuilder('qr')
                ->select('COUNT(qr.id)')
                ->andWhere('qr.quizsubmitted = :quizId')
                ->setParameter('quizId', $quiz->getIdquiz())
                ->getQuery()
                ->getSingleScalarResult();
    
            // Calculate the passing score based on your criteria
          //  multiplying the total number of questions by the passing score percentage, we obtain the passing score value.
                    $passingScore = ($quiz->getNbquestion() * 0.7);
    
            // Fetch the passed count
            $passedCount = $this->createQueryBuilder('qr')
                ->select('COUNT(qr.id)')
                ->andWhere('qr.quizsubmitted = :quizId')
                ->andWhere('qr.score >= :passingScore')
                ->setParameter('quizId', $quiz->getIdquiz())
                ->setParameter('passingScore', $passingScore)
                ->getQuery()
                ->getSingleScalarResult();
            
            // Calculate the failed count
            $failedCount = $submittedCount - $passedCount;
    
            // Update total counts
            $totalSubmittedCount += $submittedCount;
            $totalFailedCount += $failedCount;
            $totalSuccessfulCount += $passedCount;
        }
    
        // Calculate success and failure percentages
        $totalSuccessPercentage = ($totalSubmittedCount > 0) ? ($totalSuccessfulCount / $totalSubmittedCount) * 100 : 0;
        $totalFailurePercentage = ($totalSubmittedCount > 0) ? ($totalFailedCount / $totalSubmittedCount) * 100 : 0;
    
        return [
            'totalSubmittedCount' => $totalSubmittedCount,
            'totalFailedCount' => $totalFailedCount,
            'totalSuccessfulCount' => $totalSuccessfulCount,
            'totalSuccessPercentage' => $totalSuccessPercentage,
            'totalFailurePercentage' => $totalFailurePercentage,
        ];
    }
    
    
    public function getQuizData(): array
    {
        $entityManager = $this->getEntityManager();
    
        // Fetch quiz data from the repository
        $qb = $entityManager->createQueryBuilder();
        $quizData = $qb->select('q.Title AS Title, q.subject AS subject')
                       ->from('App\Entity\Quiz', 'q')
                       ->getQuery()
                       ->getResult();
    
        return $quizData;
    } 
////fetch data to table quizsubmitted teacher


public function findAllWithUserAndQuiz(): array
{
    return $this->createQueryBuilder('qr')
        ->leftJoin('qr.user', 'u')
        ->addSelect('u')
        ->leftJoin('qr.quiz', 'q')
        ->addSelect('q')
        ->getQuery()
        ->getResult();
}
}