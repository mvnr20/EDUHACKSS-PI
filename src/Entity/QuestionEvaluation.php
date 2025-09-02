<?php

namespace App\Entity;

use App\Entity\Quiz;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QuestionEvaluationRepository;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass:QuestionEvaluationRepository::class)]
class QuestionEvaluation

 
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "idquestion")]
    private ?int $idQuestion= null;
    

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "subject is required")]

    private ?string $subject= null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "question is required")]

    private ?string $question= null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "answer is required")]

    private ?string $answer= null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "description is required")]

    private ?string $description= null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "option1 is required")]

    private ?string $option1= null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "option2 is required")]

    private ?string $option2= null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "option3 is required")]

    private ?string $option3= null;

   #[ORM\ManyToOne(targetEntity: Quiz::class,inversedBy: 'questionEvaluation')]
   
   #[ORM\JoinColumn(name: "quizId", referencedColumnName: "idquiz")]
   
   private ?Quiz $quiz=null;



      public function getQuiz(): ?Quiz
   {
       return $this->quiz;
   }

   public function setQuiz(?Quiz $quiz): static
   {
       $this->quiz = $quiz;
       return $this;
   }

   public function getidQuestion(): ?int
   {
       return $this->idQuestion;
   }

   public function getSubject(): ?string
   {
       return $this->subject;
   }

   public function setSubject(string $subject): static
   {
       $this->subject = $subject;

       return $this;
   }

   public function getQuestion(): ?string
   {
       return $this->question;
   }

   public function setQuestion(string $question): static
   {
       $this->question = $question;

       return $this;
   }

   public function getAnswer(): ?string
   {
       return $this->answer;
   }

   public function setAnswer(string $answer): static
   {
       $this->answer = $answer;

       return $this;
   }

   public function getDescription(): ?string
   {
       return $this->description;
   }

   public function setDescription(string $description): static
   {
       $this->description = $description;

       return $this;
   }

   public function getOption1(): ?string
   {
       return $this->option1;
   }

   public function setOption1(string $option1): static
   {
       $this->option1 = $option1;

       return $this;
   }

   public function getOption2(): ?string
   {
       return $this->option2;
   }

   public function setOption2(string $option2): static
   {
       $this->option2 = $option2;

       return $this;
   }

   public function getOption3(): ?string
   {
       return $this->option3;
   }

   public function setOption3(string $option3): static
   {
       $this->option3 = $option3;

       return $this;
   }

   /*public function getQuizid(): ?Quiz
   {
       return $this->quizid;
   }

   public function setQuizid(?Quiz $quizid): static
   {
       $this->quizid = $quizid;

       return $this;
   }*/


   
}