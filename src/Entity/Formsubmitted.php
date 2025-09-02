<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FormSubmittedRepository; 

#[ORM\Entity(repositoryClass: FormSubmittedRepository::class)]
class Formsubmitted
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $submissionId= null;

    
    #[ORM\ManyToOne(targetEntity:Form::class,inversedBy:'formsubmitted')]
    private ?Form $form=null;

    #[ORM\ManyToOne(targetEntity:Question::class,inversedBy:'formsubmitted')]
    private ?Question $question=null;

    #[ORM\ManyToOne(targetEntity:Answer::class,inversedBy:'formsubmitted')]
    private ?Answer $answer=null;
    #[ORM\ManyToOne(targetEntity:User::class,inversedBy:'formsubmitted')]
    private ?User $user=null;

    public function getSubmissionId(): ?int
    {
        return $this->submissionId;
    }

    

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setForm(?Form $form): self
    {
        $this->form = $form;
        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;
        return $this;
    }

    public function getAnswer(): ?Answer
    {
        return $this->answer;
    }

    public function setAnswer(?Answer $answer): self
    {
        $this->answer = $answer;
        return $this;
    }
}
