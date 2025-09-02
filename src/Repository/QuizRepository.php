<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\QuestionEvaluationRepository;
use App\Entity\QuestionEvaluation;

class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }
  
    public function calculateQuizScore(array $submittedAnswers, array $correctAnswers): float
    {
        // Implement your logic to calculate the score
        // You can use repository methods or any other necessary dependencies
        // Here, I'm just providing a placeholder implementation
        $score = 0.0;

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

    public function getTotalQuestionsInQuiz(int $quizId): int
    {
        // Replace this with your actual logic to fetch the question IDs for the given quiz
        $questionIds =0;
    
        // Calculate the total number of questions
        $totalQuestions = count($questionIds);
    
        return $totalQuestions;
    }
    public function findBySearchInput($searchInput)
    {
        return $this->createQueryBuilder('q')
            ->where('q.title LIKE :searchInput')
            ->orWhere('q.subject LIKE :searchInput')
            ->orWhere('q.nbQuestions LIKE :searchInput')
            ->setParameter('searchInput', '%' . $searchInput . '%')
            ->getQuery()
            ->getResult();
    }


}
