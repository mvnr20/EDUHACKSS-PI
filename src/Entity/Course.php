<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Course
 *
 * @ORM\Table(name="course", indexes={@ORM\Index(name="idSubject", columns={"idSubject"}), @ORM\Index(name="idTeacher", columns={"idTeacher"})})
 * @ORM\Entity
 */
#[ORM\Entity]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Type is required")]
    private ?string  $title;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(message: "Type is required")]
    private ?string $course;

    #[ORM\Column(name: "Created")]
    private ?\DateTime $created;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "idTeacher", referencedColumnName: "id")]
    private $idteacher;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(name: "idSubject", referencedColumnName: "id")]
    private $idsubject;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCourse(): ?string
    {
        return $this->course;
    }

    public function setCourse(string $course): static
    {
        $this->course = $course;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getIdteacher(): ?User
    {
        return $this->idteacher;
    }

    public function setIdteacher(?User $idteacher): static
    {
        $this->idteacher = $idteacher;

        return $this;
    }

    public function getIdsubject(): ?Subject
    {
        return $this->idsubject;
    }

    public function setIdsubject(?Subject $idsubject): static
    {
        $this->idsubject = $idsubject;

        return $this;
    }
}
