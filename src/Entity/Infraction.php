<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InfractionRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: InfractionRepository::class)]
class Infraction
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Infraction_id", type: "integer")]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private ?User $user;

    #[ORM\Column(name: "infraction_date", type: "date")]
    #[Assert\NotNull(message: "Infraction Date cannot be blank")]
    private ?\DateTimeInterface $infractionDate;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Infraction Type cannot be blank")]
    private ?string $infractionType;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInfractionDate(): ?\DateTimeInterface
    {
        return $this->infractionDate;
    }

    public function setInfractionDate(?DateTimeInterface $infractionDate): self
    {
        $this->infractionDate = $infractionDate;
        return $this;
    }

    public function getInfractionType(): ?string
    {
        return $this->infractionType;
    }

    public function setInfractionType(?string $infractionType): self
    {
        $this->infractionType = $infractionType;
        return $this;
    }
}
