<?php

namespace App\Entity;
use DateTimeImmutable;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\QuizRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass:QuizRepository::class)]
class Quiz


{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $idquiz= null;
    

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title is required")]
    private ?string $Title= null;
  

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "subject is required")]
    private ?string $subject= null ;
 
    #[ORM\Column(name: "NbQuestion")]
    #[Assert\NotBlank(message: "Number of Question is required")]
    private ?int $NbQuestion= null ;


    #[ORM\Column(name: "DateCreated")]
    #[Assert\NotBlank(message: "The Date is required")]
    private ?\DateTimeImmutable $Datecreated ;
    

    #[ORM\OneToMany(targetEntity:QuestionEvaluation::class,mappedBy:'quiz')]
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
    

    public function addQuestionEvaluation(QuestionEvaluation $questionEvaluation): self
    {
        if (!$this->questionEvaluation->contains($questionEvaluation)) {
            $this->questionEvaluation[] = $questionEvaluation;
            $questionEvaluation->setQuiz($this);
        }
    
        return $this;
    }
    
    public function getIdquiz(): ?int
    {
        return $this->idquiz;
    }

    public function getTitle(): ?string
    {
        return $this->Title;
    }

    public function setTitle(?string $Title): static
    {
        $this->Title = $Title;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getNbquestion(): ?int
    {
        return $this->NbQuestion;
    }

    public function setNbquestion(int $NbQuestion): static
    {
        $this->NbQuestion = $NbQuestion;

        return $this;
    }

    public function getDatecreated(): ?\DateTimeImmutable
    {
        return $this->Datecreated;
    }
    
    
    public function setDatecreated(?\DateTime $Datecreated): self
{
    if ($Datecreated !== null) {
        // Convert DateTime to DateTimeImmutable
        $immutableDateCreated = \DateTimeImmutable::createFromMutable($Datecreated);
        
        // Assign the converted DateTimeImmutable object
        $this->Datecreated = $immutableDateCreated;
    } else {
        // If $Datecreated is null, set the property to null as well
        $this->Datecreated = null;
    }

    return $this;
}

#[ORM\OneToMany(targetEntity:QuizResult::class,mappedBy:'quiz')]
private Collection $quizResults;


public function getquizResults(): Collection
{
    return $this->quizResults;
}


public function addQuizResult(QuizResult $QuizResult): self
{
    if (!$this->QuizResult->contains($QuizResult)) {
        $this->QuizResult[] = $QuizResult;
        $QuizResult->setQuiz($this);
    }

    return $this;
}


}