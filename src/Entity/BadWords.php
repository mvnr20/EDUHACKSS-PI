<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BadWordsRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: BadWordsRepository::class)]
class BadWords
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "word_id")]
    private $wordId; // Update property name to match the database column name

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Word cannot be blank")]
    private $word;

    public function getWordId(): ?int
    {
        return $this->wordId; // Update property name to match the database column name
    }

    public function getWord(): ?string
    {
        return $this->word;
    }

    public function setWord(?string $word): self
    {
        $this->word = $word;
        return $this;
    }
}
