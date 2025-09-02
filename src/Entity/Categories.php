<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoriesRepository;

#[ORM\Entity(repositoryClass: CategoriesRepository::class)]
class Categories
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $categ_id = null;

    #[ORM\OneToMany(mappedBy: 'articles_id', targetEntity: Article::class)]
    private Collection $articles_id; // Changed name to match the database schema

    #[ORM\Column(type: "string")]
    private ?string $name = null;

    public function __construct()
    {
        $this->articles_id = new ArrayCollection(); // Adjusted to match the property name
    }

    public function getId(): ?int
    {
        return $this->categ_id;
    }

    /**
     * @return Collection<int, Article>
     */
    public function getArticles(): Collection
    {
        return $this->articles_id; // Adjusted to match the property name
    }

    public function removeArticle(Article $article): self
    {
        if ($this->articles_id->removeElement($article)) { // Adjusted to match the property name
            // set the owning side to null (unless already changed)
            if ($article->getCategory() === $this) {
                $article->setCategory(null);
            }
        }
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }


    public function addArticle(Article $article): self
    {
        if (!$this->articles_id->contains($article)) { // Adjusted to match the property name
            $this->articles_id[] = $article; // Adjusted to match the property name
            $article->setCategory($this);
            $article->setCategoryId($this->getId()); // Set the article ID in the category
        }
        return $this;
    }
}
