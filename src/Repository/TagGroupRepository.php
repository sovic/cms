<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sovic\Cms\Entity\TagGroup;
use Sovic\Cms\Tag\TagName;
use Sovic\Common\Entity\Project;

/**
 * @method TagGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagGroup[]    findAll()
 * @method TagGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagGroupRepository extends EntityRepository
{
    /**
     * Query builder for groups ordered by position and name, usable in EntityType 'query_builder'.
     */
    public function createOrderedQueryBuilder(?Project $project = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('g');
        if ($project !== null) {
            $qb->andWhere('g.project = :project');
            $qb->setParameter('project', $project);
        }
        $qb->addOrderBy('g.position', 'ASC');
        $qb->addOrderBy('g.name', 'ASC');

        return $qb;
    }

    /**
     * @return TagGroup[]
     */
    public function findOrdered(?Project $project = null): array
    {
        return $this->createOrderedQueryBuilder($project)->getQuery()->getResult();
    }

    public function findOneById(int $id, ?Project $project = null): ?TagGroup
    {
        $criteria = ['id' => $id];
        if ($project !== null) {
            $criteria['project'] = $project;
        }

        return $this->findOneBy($criteria);
    }

    public function findOneByUrlId(string $urlId, ?Project $project = null): ?TagGroup
    {
        $criteria = ['urlId' => $urlId];
        if ($project !== null) {
            $criteria['project'] = $project;
        }

        return $this->findOneBy($criteria);
    }

    /**
     * Unique ASCII url_id within project, adds numeric suffix on collision (e.g. 'regiony-2').
     */
    public function createUniqueUrlId(string $name, ?Project $project = null, ?int $excludeId = null): string
    {
        $maxLength = 100;
        $base = substr(TagName::toUrlId($name), 0, $maxLength);
        if ($base === '') {
            $base = 'group';
        }

        $urlId = $base;
        $i = 2;
        while (($existing = $this->findOneByUrlId($urlId, $project)) !== null && $existing->getId() !== $excludeId) {
            $suffix = '-' . $i;
            $urlId = rtrim(substr($base, 0, $maxLength - strlen($suffix)), '-') . $suffix;
            $i++;
        }

        return $urlId;
    }

    public function getNextPosition(?Project $project = null): int
    {
        $qb = $this->createQueryBuilder('g');
        $qb->select('MAX(g.position)');
        if ($project !== null) {
            $qb->andWhere('g.project = :project');
            $qb->setParameter('project', $project);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() + 1;
    }
}
