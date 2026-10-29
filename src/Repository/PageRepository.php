<?php

namespace Sovic\Cms\Repository;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Entity\PageGroup;
use Sovic\Common\DataList\Enum\VisibilityId;
use Sovic\Common\DataList\SearchRequestInterface;
use Sovic\Common\Entity\Project as ProjectEntity;
use Sovic\Common\Project\Project;
use SortDirection;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, ?array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends EntityRepository
{
    /**
     * @return Page[]
     */
    public function findPublic(Project $project, ?int $limit = null, ?int $offset = null): array
    {
        return $this->findBy(
            [
                'project' => $project->entity,
                'isPublic' => true,
            ],
            ['id' => 'DESC'],
            $limit,
            $offset,
        );
    }

    public function findBySearchRequest(SearchRequestInterface $searchRequest)
    {
        $qb = $this->prepareQueryBuilder($searchRequest);
        $qb->addOrderBy('p.id', SortDirection::Descending);

        $qb->setFirstResult($searchRequest->getOffset());
        $qb->setMaxResults($searchRequest->getLimit());

        return $qb->getQuery()->getResult();
    }

    public function countBySearchRequest(SearchRequestInterface $searchRequest): int
    {
        $qb = $this->prepareQueryBuilder($searchRequest);
        $qb->select('COUNT(p.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Page[]
     */
    public function findByGroupId(int $groupId, SearchRequestInterface $searchRequest): array
    {
        $qb = $this->buildGroupQuery($groupId, $searchRequest);
        $qb->addOrderBy('p.name', SortDirection::Ascending);
        $qb->setFirstResult($searchRequest->getOffset());
        $qb->setMaxResults($searchRequest->getLimit());

        return $qb->getQuery()->getResult();
    }

    public function countByGroupId(int $groupId, SearchRequestInterface $searchRequest): int
    {
        $qb = $this->buildGroupQuery($groupId, $searchRequest);
        $qb->select('COUNT(p.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns counts keyed by PageGroup id.
     *
     * @param PageGroup[] $groups
     * @return array<int, int>
     */
    public function countPerGroup(array $groups): array
    {
        if (empty($groups)) {
            return [];
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('IDENTITY(p.pageGroup) AS group_id, COUNT(p.id) AS cnt');
        $qb->from(Page::class, 'p');
        $qb->where('p.pageGroup IN (:groups)');
        $qb->setParameter('groups', $groups);
        $qb->groupBy('p.pageGroup');

        $counts = [];
        foreach ($qb->getQuery()->getResult() as $row) {
            $counts[(int) $row['group_id']] = (int) $row['cnt'];
        }

        return $counts;
    }

    public function countUnsorted(): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('COUNT(p.id)');
        $qb->from(Page::class, 'p');
        $qb->where('p.pageGroup IS NULL');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function buildGroupQuery(int $groupId, ?SearchRequestInterface $searchRequest = null): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p');
        $qb->from(Page::class, 'p');

        if ($groupId === 0) {
            $qb->where('p.pageGroup IS NULL');
        } else {
            $qb->where('IDENTITY(p.pageGroup) = :groupId');
            $qb->setParameter('groupId', $groupId);
        }

        if ($searchRequest && $searchRequest->getSearch()) {
            $qb->andWhere('(p.name LIKE :search OR p.heading LIKE :search)');
            $qb->setParameter('search', '%' . $searchRequest->getSearch() . '%');
        }

        return $qb;
    }

    private function prepareQueryBuilder(SearchRequestInterface $searchRequest): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p');
        $qb->from(Page::class, 'p');

        if ($searchRequest->getSearch()) {
            $qb->andWhere('p.name LIKE :search OR p.heading LIKE :search');
            $qb->setParameter('search', '%' . $searchRequest->getSearch() . '%');
        }

        switch ($searchRequest->getVisibilityId()) {
            case VisibilityId::Public:
                $qb->andWhere('p.isPublic = true');
                break;

            case VisibilityId::Private:
                $qb->andWhere('p.isPublic = false');
                break;

            case VisibilityId::Deleted:
                throw new InvalidArgumentException('Deleted visibility is not supported for pages.');

            case VisibilityId::All:
                // no additional conditions
                break;
        }

        return $qb;
    }

    /**
     * Slug from page name, unique within project (-2, -3, … suffix on clash).
     */
    public function createUniqueUrlId(
        string         $name,
        ?ProjectEntity $project,
        ?int           $excludeId = null,
    ): string {
        $maxLength = 200;

        $slugify = new Slugify();
        $slugify->activateRuleSet('default');
        $base = substr($slugify->slugify($name), 0, $maxLength);
        $base = rtrim($base, '-');
        if ($base === '') {
            $base = 'page';
        }

        $urlId = $base;
        $i = 2;
        while ($this->urlIdExists($urlId, $project, $excludeId)) {
            $suffix = '-' . $i;
            $urlId = rtrim(substr($base, 0, $maxLength - strlen($suffix)), '-') . $suffix;
            $i++;
        }

        return $urlId;
    }

    private function urlIdExists(
        string         $urlId,
        ?ProjectEntity $project,
        ?int           $excludeId,
    ): bool {
        $qb = $this->createQueryBuilder('p');
        $qb->select('COUNT(p.id)');
        $qb->where('p.urlId = :url_id');
        $qb->setParameter('url_id', $urlId);
        if ($project !== null) {
            $qb->andWhere('p.project = :project');
            $qb->setParameter('project', $project);
        } else {
            $qb->andWhere('p.project IS NULL');
        }
        if ($excludeId !== null) {
            $qb->andWhere('p.id != :exclude_id');
            $qb->setParameter('exclude_id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }
}
