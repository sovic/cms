<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Tag\TagName;
use Sovic\Common\DataList\SearchRequestInterface;
use Sovic\Common\Entity\Project;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends EntityRepository
{
    /**
     * @return Tag[]
     */
    public function findBySearchRequest(
        SearchRequestInterface $searchRequest,
        ?Project $project = null,
    ): array {
        $qb = $this->buildQuery($searchRequest, $project);
        $qb->orderBy('t.name', 'ASC');
        $qb->setFirstResult($searchRequest->getOffset());
        $qb->setMaxResults($searchRequest->getLimit());

        return $qb->getQuery()->getResult();
    }

    public function countBySearchRequest(
        SearchRequestInterface $searchRequest,
        ?Project $project = null,
    ): int {
        $qb = $this->buildQuery($searchRequest, $project);
        $qb->select('COUNT(t.id)');

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException) {
            return 0;
        }
    }

    private function buildQuery(
        SearchRequestInterface $searchRequest,
        ?Project $project,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('t');

        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }

        $search = $searchRequest->getSearch();
        if ($search) {
            $qb->andWhere('t.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }

        return $qb;
    }

    /**
     * @return string[]
     */
    public function findBySearch(string $search = '', int $limit = 30): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t.name');
        $qb->from(Tag::class, 't');
        $qb->where('t.name IS NOT NULL');
        if ($search !== '') {
            $qb->andWhere('t.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('t.name', 'ASC');
        $qb->setMaxResults($limit);

        return array_column($qb->getQuery()->getResult(), 'name');
    }

    /**
     * Unique ASCII url_id for tag name, adds numeric suffix on collision (e.g. 'caj-2').
     */
    public function createUniqueUrlId(string $name): string
    {
        $maxLength = 100;
        $base = substr(TagName::toUrlId($name), 0, $maxLength);
        if ($base === '') {
            $base = 'tag';
        }

        $urlId = $base;
        $i = 2;
        while ($this->findOneBy(['urlId' => $urlId]) !== null) {
            $suffix = '-' . $i;
            $urlId = rtrim(substr($base, 0, $maxLength - strlen($suffix)), '-') . $suffix;
            $i++;
        }

        return $urlId;
    }
}
