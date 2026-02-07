<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Sovic\Cms\Entity\Page;
use Sovic\Common\DataList\Enum\VisibilityId;
use Sovic\Common\DataList\SearchRequestInterface;
use Sovic\Common\Project\Project;

class PageRepository extends EntityRepository
{
    /**
     * @return Page[]
     */
    public function findPublic(Project $project, int $limit = null, int $offset = null): array
    {
        return $this->findBy(
            [
                'project' => $project->entity,
                'public' => true,
            ],
            ['id' => 'DESC'],
            $limit,
            $offset,
        );
    }

    public function findBySearchRequest(SearchRequestInterface $searchRequest)
    {
        $qb = $this->prepareQueryBuilder($searchRequest);
        $qb->addOrderBy('p.id', 'DESC');

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

    private function prepareQueryBuilder(SearchRequestInterface $searchRequest): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('p')->from(Page::class, 'p');

        if ($searchRequest->getSearch()) {
            $qb->andWhere('p.name LIKE :search OR p.heading LIKE :search')
                ->setParameter('search', '%' . $searchRequest->getSearch() . '%');
        }

        switch ($searchRequest->getVisibilityId()) {
            case VisibilityId::Public:
                $qb->andWhere('p.public = true');
                break;

            case VisibilityId::Private:
                $qb->andWhere('p.public = false');
                break;

            case VisibilityId::Deleted:
                throw new InvalidArgumentException('Deleted visibility is not supported for pages.');

            case VisibilityId::All:
                // no additional conditions
                break;
        }

        return $qb;
    }
}
