<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\FormRepository; 
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FormRepository::class)]
class Form
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Title is required")]
    private ?string $title = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Type is required")]
    private ?string $type = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Status is required")]
    private ?string $status = null;
    #[ORM\OneToMany(targetEntity:Question::class,mappedBy:'form')]
    private Collection $Questions;

    #[ORM\OneToMany(targetEntity:Formsubmitted::class,mappedBy:'form')]
    private Collection $FormSubmitted;

    public function __construct()
    {
        $this->Questions = new ArrayCollection();
        $this->FormSubmitted = new ArrayCollection();
    }
    
    public function getQuestion(): Collection
    {
        return $this->Questions;
    }

    public function getFormsubmitted(): Collection
    {
        return $this->FormSubmitted;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title ?? ''; // Assign an empty string if $title is null

        return $this;
    }
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type ?? ''; // Assign an empty string if $type is null

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }
    
    public function setStatus(?string $status): static
    {
        $this->status = $status ?? ''; // Assign an empty string if $status is null

        return $this;
    }


}
