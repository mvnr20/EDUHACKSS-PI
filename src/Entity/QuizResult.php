<?php

namespace App\Entity;
use App\Entity\Quiz;

use App\Repository\QuizResultRepository;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection; // Add this line
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizResultRepository::class)]
class QuizResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $userid = null;

    #[ORM\Column]
    private ?float $score = null;

    #[ORM\Column(length: 255)]
    private ?string $questionnumber = null;

    #[ORM\Column]
    private ?int $quizsubmitted = null;

    #[ORM\OneToMany(targetEntity:QuestionEvaluation::class,mappedBy:'QuizResult')]
    private Collection $questionEvaluation;
    public function __construct()
    {
        $this->questionEvaluation = new ArrayCollection();
    }
/**
     * @return Collection<String, QuestionEvaluation>
     */

    public function getQuestionEvaluation(): Collection
    {
        return $this->questionEvaluation;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(int $userid): static
    {
        $this->userid = $userid;

        return $this;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(float $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getQuestionnumber(): ?string
    {
        return $this->questionnumber;
    }

    public function setQuestionnumber(string $questionnumber): static
    {
        $this->questionnumber = $questionnumber;

        return $this;
    }

    public function getQuizsubmitted(): ?int
    {
        return $this->quizsubmitted;
    }

    public function setQuizSubmitted(Quiz $quizSubmitted): static
    {
        $this->quizSubmitted = $quizSubmitted;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Quiz::class, inversedBy: 'quizResults')]
    #[ORM\JoinColumn(name: "quizsubmitted", referencedColumnName: "idquiz")]
    private ?Quiz $quiz;

    public function getQuiz(): ?Quiz
    {
        return $this->quiz;
    }
    
    public function setQuiz(?Quiz $quiz): self
    {
        $this->quiz = $quiz;
        return $this;
    }

#[ORM\ManyToOne(targetEntity: User::class,inversedBy: 'quizResults')]
   
#[ORM\JoinColumn(name: "userid", referencedColumnName: "id")]

private ?User $user=null;



public function getUser(): ?User
{
    return $this->user;
}

public function setUser(?User $user): self
{
    $this->user = $user;

    return $this;
}

}