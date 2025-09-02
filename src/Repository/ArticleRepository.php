<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;


/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }
    public function findAllArticles(): array
    {
        return $this->findAll();

    }

    public function deleteArticleById(int $id): void
    {
        $entityManager = $this->getEntityManager();
        $article = $entityManager->getReference(Article::class, $id);
        $entityManager->remove($article);
        $entityManager->flush();
    }

    public function updateArticle(Article $article, array $data): void
    {
        // Update article properties with new data
        $article->setTitle($data['title']);
        $article->setBody($data['body']);

        // Persist the changes
        $entityManager = $this->getEntityManager();
        $entityManager->persist($article);
        $entityManager->flush();
    }

    public function findMostViewedArticle(): ?Article
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.views', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findMostViewedArticles($limit = 3)
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.views', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function likeArticle(Article $article): void
    {
        $article->setLikes($article->getLikes() + 1);
        $this->_em->persist($article);
        $this->_em->flush();
    }

    public function dislikeArticle(Article $article): void
    {
        $article->setDislikes($article->getDislikes() + 1);
        $this->_em->persist($article);
        $this->_em->flush();
    }

    // ArticleRepository.php

public function increaseViewCount(Article $article): void
{
    $article->setViews($article->getViews() + 1);
    $this->_em->persist($article);
    $this->_em->flush();
}

// Inside the ArticleRepository class
public function findBySearchQuery(string $searchQuery): array
{
    $qb = $this->createQueryBuilder('a');

    $qb->where(
        $qb->expr()->orX(
            $qb->expr()->like('a.title', ':searchQuery'),
            $qb->expr()->like('a.body', ':searchQuery')
        )
    )
    ->setParameter('searchQuery', '%' . $searchQuery . '%');

    return $qb->getQuery()->getResult();
}

}

