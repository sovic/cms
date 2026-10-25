<?php

namespace Sovic\Cms\Repository;

use DateTimeImmutable;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Entity\PostTag;
use Sovic\Cms\Post\PostSearchRequest;
use Sovic\Common\DataList\Enum\VisibilityId;
use Sovic\Common\Project\Project;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends EntityRepository
{
    /**
     * @return Post[]
     */
    public function findPublic(
        ?Project $project = null,
        int      $limit = null,
        int      $offset = null,
    ): array {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p');
        $qb->from(Post::class, 'p');
        if ($project) {
            $qb->andWhere('p.project = :project');
            $qb->setParameter(':project', $project->entity);
        } else {
            $qb->andWhere('p.project IS NULL');
        }

        $this->addPublishedCriteria($qb);
        $qb->andWhere('p.privateSlug IS NULL');

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

    public function countPublic(?Project $project = null): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(p.id)');
        $qb->from(Post::class, 'p');
        if ($project) {
            $qb->andWhere('p.project = :project');
            $qb->setParameter(':project', $project->entity);
        } else {
            $qb->andWhere('p.project IS NULL');
        }

        $this->addPublishedCriteria($qb);
        $qb->andWhere('p.privateSlug IS NULL');

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }

    /**
     * @return Post[]
     */
    public function findPublicByTag(Project $project, Tag $tag, int $limit = null, int $offset = null): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p');
        $qb->from(Post::class, 'p');
        $qb->leftJoin(PostTag::class, 'tp', Join::WITH, 'tp.postId = p.id');
        $qb->andWhere('p.project = :project');

        $this->addPublishedCriteria($qb);
        $qb->andWhere('p.privateSlug IS NULL');

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
        $qb->select('count(p.id)');
        $qb->from(Post::class, 'p');
        $qb->leftJoin(PostTag::class, 'tp', Join::WITH, 'tp.postId = p.id');
        $qb->andWhere('p.project = :project');

        $this->addPublishedCriteria($qb);
        $qb->andWhere('p.privateSlug IS NULL');

        $qb->andWhere('tp.tagId = :tag_id');
        $qb->setParameter(':project', $project->entity);
        $qb->setParameter(':tag_id', $tag->getId());

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }

    /**
     * @return Post[]
     */
    public function findPublicByMonth(
        Project $project,
        int     $year,
        int     $month,
        int     $limit = null,
        int     $offset = null
    ): array {
        $startDate = new DateTimeImmutable("$year-$month-01T00:00:00");
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p');
        $qb->from(Post::class, 'p');
        $qb->andWhere('p.project = :project');

        $this->addPublishedCriteria($qb);
        $qb->andWhere('p.privateSlug IS NULL');

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
        $qb->select('count(p.id)');
        $qb->from(Post::class, 'p');
        $qb->andWhere('p.project = :project');

        $this->addPublishedCriteria($qb);
        $qb->andWhere('p.privateSlug IS NULL');

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

    /**
     * @return Post[]
     */
    public function findByRequest(PostSearchRequest $request): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p');
        $qb->from(Post::class, 'p');
        $this->updateCriteria($qb, $request);

        foreach ($request->getPostSort()->getOrderBy() as $field => $direction) {
            $qb->addOrderBy('p.' . $field, $direction);
        }

        $qb->setMaxResults($request->getLimit());
        $qb->setFirstResult($request->getOffset());

        return $qb->getQuery()->getResult();
    }

    public function countByRequest(PostSearchRequest $request): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('count(p.id)');
        $qb->from(Post::class, 'p');
        $this->updateCriteria($qb, $request);

        try {
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }

    private function updateCriteria(QueryBuilder $qb, PostSearchRequest $request): void
    {
        if ($request->getProject()) {
            $qb->andWhere('p.project = :project');
            $qb->setParameter(':project', $request->getProject()->entity);
        }

        switch ($request->getVisibilityId()) {
            case VisibilityId::Public:
                $this->addPublishedCriteria($qb);
                $qb->andWhere('p.privateSlug IS NULL');
                break;
            case VisibilityId::Private:
                $qb->andWhere('p.privateSlug IS NOT NULL');
                break;
            case VisibilityId::Deleted:
                // posts have no soft delete
                $qb->andWhere('1 = 0');
                break;
            case VisibilityId::All:
                break;
        }

        if ($request->getTag()) {
            $qb->leftJoin(PostTag::class, 'tp', Join::WITH, 'tp.postId = p.id');
            $qb->andWhere('tp.tagId = :tag_id');
            $qb->setParameter(':tag_id', $request->getTag()->getId());
        }
        if ($request->getSearch()) {
            $qb->andWhere('p.name LIKE :search OR p.heading LIKE :search');
            $qb->setParameter(':search', '%' . addcslashes($request->getSearch(), '%_') . '%');
        }
        if ($request->getMaxId()) {
            $qb->andWhere('p.id < :max_id');
            $qb->setParameter(':max_id', $request->getMaxId());
        }
    }

    /**
     * Published = public flag set and publish date reached (future publish date = scheduled post).
     */
    private function addPublishedCriteria(QueryBuilder $qb): void
    {
        $qb->andWhere('p.public = 1');
        $qb->andWhere('p.publishDate IS NOT NULL');
        $qb->andWhere('p.publishDate <= :published_now');
        $qb->setParameter(':published_now', new DateTimeImmutable());
    }
}
