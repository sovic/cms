<?php

namespace SovicCms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use SovicCms\Entity\Post;
use SovicCms\Entity\Tag;
use SovicCms\Entity\TagPost;

class PostRepository extends EntityRepository
{
    public function findPublic(int $limit = null, int $offset = null): array
    {
        return $this->findBy(
            ['public' => true],
            ['publishDate' => 'DESC', 'id' => 'DESC'],
            $limit,
            $offset,
        );
    }

    public function countPublic(): int
    {
        return $this->count(['public' => true]);
    }

    public function findPublicByTag(Tag $tag, int $limit = null, int $offset = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')->from(Post::class, 'p');
        $qb->leftJoin(TagPost::class, 'tp', Join::WITH, 'tp.postId = p.id');
        $qb->andWhere('p.public = 1');
        $qb->andWhere('tp.tagId = :tag_id');
        $qb->setParameter(':tag_id', $tag->getId());
        $qb->addOrderBy('p.publishDate', 'DESC');
        $qb->addOrderBy('p.id', 'DESC');
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getResult();
    }

    public function countPublicByTag(Tag $tag): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(p.id)')->from(Post::class, 'p');
        $qb->leftJoin(TagPost::class, 'tp', Join::WITH, 'tp.postId = p.id');
        $qb->andWhere('p.public = 1');
        $qb->andWhere('tp.tagId = :tag_id');
        $qb->setParameter(':tag_id', $tag->getId());

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb->getQuery()->getSingleScalarResult();
    }
}
