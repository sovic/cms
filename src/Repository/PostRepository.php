<?php

namespace Sovic\Cms\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Entity\PostTag;
use Sovic\Cms\Project\Project;

class PostRepository extends EntityRepository
{
    public function findPublic(Project $project, int $limit = null, int $offset = null): array
    {
        return $this->findBy(
            [
                'project' => $project->entity,
                'public' => true,
            ],
            ['publishDate' => 'DESC', 'id' => 'DESC'],
            $limit,
            $offset,
        );
    }

    public function countPublic(Project $project): int
    {
        return $this->count(
            [
                'project' => $project->entity,
                'public' => true,
            ]
        );
    }

    public function findPublicByTag(Project $project, Tag $tag, int $limit = null, int $offset = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')->from(Post::class, 'p');
        $qb->leftJoin(PostTag::class, 'tp', Join::WITH, 'tp.postId = p.id');
        $qb->andWhere('p.project = :project');
        $qb->andWhere('p.public = 1');
        $qb->andWhere('tp.tagId = :tag_id');
        $qb->setParameter(':project', $project->entity);
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

    public function countPublicByTag(Project $project, Tag $tag): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(p.id)')->from(Post::class, 'p');
        $qb->leftJoin(PostTag::class, 'tp', Join::WITH, 'tp.postId = p.id');
        $qb->andWhere('p.project = :project');
        $qb->andWhere('p.public = 1');
        $qb->andWhere('tp.tagId = :tag_id');
        $qb->setParameter(':project', $project->entity);
        $qb->setParameter(':tag_id', $tag->getId());

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            // TODO log

            return 0;
        }
    }

    public function findPublicByMonth(Project $project, int $year, int $month, int $limit = null, int $offset = null)
    {
        $startDate = new DateTimeImmutable("$year-$month-01T00:00:00");
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')->from(Post::class, 'p');
        $qb->andWhere('p.project = :project');
        $qb->andWhere('p.public = 1');
        $qb->andWhere('p.publishDate BETWEEN :start_date AND :end_date');
        $qb->setParameter(':project', $project->entity);
        $qb->setParameter(':start_date', $startDate->format('Y-m-d H:i:s'));
        $qb->setParameter(':end_date', $endDate->format('Y-m-d H:i:s'));
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

    public function countPublicByMonth(Project $project, int $year, int $month): int
    {
        $startDate = new DateTimeImmutable("$year-$month-01T00:00:00");
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(p.id)')->from(Post::class, 'p');
        $qb->andWhere('p.project = :project');
        $qb->andWhere('p.public = 1');
        $qb->andWhere('p.publishDate BETWEEN :start_date AND :end_date');
        $qb->setParameter(':project', $project->entity);
        $qb->setParameter(':start_date', $startDate->format('Y-m-d H:i:s'));
        $qb->setParameter(':end_date', $endDate->format('Y-m-d H:i:s'));

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }
}
