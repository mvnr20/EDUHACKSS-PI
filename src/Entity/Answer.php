<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\AnswerRepository; 
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

 #[ORM\Entity(repositoryClass: AnswerRepository::class)]

class Answer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length:50)]
    #[Assert\NotBlank(message: "Answer is required")]
    private ?string $predefans=null;

    #[ORM\ManyToOne(targetEntity:Question::class,inversedBy:'answer')]
    private ?Question $question=null;

    #[ORM\OneToMany(targetEntity:Formsubmitted::class, mappedBy:'answer')]
    private ?Collection $FormSubmitted = null;

    public function __construct()
    {
        $this->FormSubmitted = new ArrayCollection();
    }
    public function getFormsubmitted(): ?Collection
    {
        return $this->FormSubmitted;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPredefans(): ?string
    {
        return $this->predefans;
    }

    public function setPredefans(?string $predefans): self
    {
        $this->predefans = $predefans ?? ''; // Assign an empty string if $predefans is null
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
}
