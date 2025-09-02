<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\QuestionRepository; 
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length:70)]
    #[Assert\NotBlank(message: "Question is required")]
    private ?string $text=null;

    #[ORM\ManyToOne(targetEntity:Form::class,inversedBy:'question')]
    private ?Form $form=null;

    #[ORM\OneToMany(targetEntity:Answer::class,mappedBy:'question')]
    private Collection $Answers;
    #[ORM\OneToMany(targetEntity:Formsubmitted::class,mappedBy:'question')]
    private Collection $FormSubmitted;
    
    public function __construct()
    {
        $this->Answers = new ArrayCollection();
        $this->FormSubmitted = new ArrayCollection();
    }
    
    public function getFormsubmitted(): Collection
    {
        return $this->FormSubmitted;
    }
    public function getAnswer(): Collection
    {
        return $this->Answers;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text ?? ''; // Assign an empty string if $text is null
        return $this;
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
}
