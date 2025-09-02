<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentsRepository;

#[ORM\Entity(repositoryClass:CommentsRepository::class)]
class Comments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $idcom = null;

    #[ORM\Column(length:255)]
    private ?string $content = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $likes = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $dislikes = 0;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'article_id', referencedColumnName: 'id')]
    private ?Article $article = null;

    public function getIdcom(): ?int
    {
        return $this->idcom;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): void
    {
        $this->likes = $likes;
    }

    public function getDislikes(): int
    {
        return $this->dislikes;
    }

    public function setDislikes(int $dislikes): void
    {
        $this->dislikes = $dislikes;
    }
}
