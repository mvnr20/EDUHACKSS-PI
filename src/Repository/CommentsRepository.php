<?php

namespace App\Repository;

use App\Entity\Comments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Article;

class CommentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comments::class);
    }

    /**
     * Find all comments associated with a specific article.
     *
     * @param int $articleId The ID of the article
     * @return Comments[] Returns an array of Comments objects
     */
    public function findAllByArticle(int $articleId): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.article = :articleId')
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->getResult();
    }

    public function addComment(Comments $comment): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($comment);
        $entityManager->flush();
    }

    public function editComment(Comments $comment): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($comment);
        $entityManager->flush();
    }

    public function deleteComment(Comments $comment): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($comment);
        $entityManager->flush();
    }

    public function findRecentComments(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countCommentsForArticle(int $articleId): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->andWhere('c.article = :articleId')
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function deleteCommentsForArticle(int $articleId): void
    {
        $this->createQueryBuilder('c')
            ->delete()
            ->andWhere('c.article = :articleId')
            ->setParameter('articleId', $articleId)
            ->getQuery()
            ->execute();
    }

    public function incrementLikes(Comments $comment): void
    {
        $comment->setLikes($comment->getLikes() + 1);
        $this->_em->flush();
    }

    public function decrementLikes(Comments $comment): void
    {
        $comment->setLikes($comment->getLikes() - 1);
        $this->_em->flush();
    }

    public function incrementDislikes(Comments $comment): void
    {
        $comment->setDislikes($comment->getDislikes() + 1);
        $this->_em->flush();
    }

    public function decrementDislikes(Comments $comment): void
    {
        $comment->setDislikes($comment->getDislikes() - 1);
        $this->_em->flush();
    }

    public function mostLikedComment(array $comments): ?Comments
    {
        $mostLiked = null;
        $maxLikes = 0;

        foreach ($comments as $comment) {
            if ($comment->getLikes() > $maxLikes) {
                $mostLiked = $comment;
                $maxLikes = $comment->getLikes();
            }
        }

        return $mostLiked;
    }
}
