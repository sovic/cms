<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Sovic\Cms\Entity\PageTag;
use Sovic\Cms\Entity\PostTag;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Entity\TagGroup;
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
     * @param int|null $groupId null = all tags, 0 = tags without group, >0 = tags in given group
     * @return Tag[]
     */
    public function findBySearchRequest(
        SearchRequestInterface $searchRequest,
        ?Project               $project = null,
        ?int                   $groupId = null,
    ): array {
        $qb = $this->buildQuery($searchRequest, $project, $groupId);
        $qb->leftJoin('t.tagGroup', 'g');
        $qb->addSelect('g');
        $qb->orderBy('t.name', 'ASC');
        $qb->setFirstResult($searchRequest->getOffset());
        $qb->setMaxResults($searchRequest->getLimit());

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int|null $groupId null = all tags, 0 = tags without group, >0 = tags in given group
     */
    public function countBySearchRequest(
        SearchRequestInterface $searchRequest,
        ?Project               $project = null,
        ?int                   $groupId = null,
    ): int {
        $qb = $this->buildQuery($searchRequest, $project, $groupId);
        $qb->select('COUNT(t.id)');

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            return 0;
        }
    }

    private function buildQuery(
        SearchRequestInterface $searchRequest,
        ?Project               $project,
        ?int                   $groupId = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('t');

        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }

        if ($groupId === 0) {
            $qb->andWhere('t.tagGroup IS NULL');
        } elseif ($groupId !== null) {
            $qb->andWhere('IDENTITY(t.tagGroup) = :groupId');
            $qb->setParameter('groupId', $groupId);
        }

        $search = $searchRequest->getSearch();
        if ($search) {
            $qb->andWhere('(t.name LIKE :search OR t.urlId LIKE :search)');
            $qb->setParameter('search', '%' . $search . '%');
        }

        return $qb;
    }

    /**
     * Tags in group ordered by name.
     *
     * @return Tag[]
     */
    public function findByGroup(TagGroup $group, bool $onlyPublic = false): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.tagGroup = :group');
        $qb->setParameter('group', $group);
        if ($onlyPublic) {
            $qb->andWhere('t.isPublic = 1');
            $qb->andWhere('t.privateSlug IS NULL');
        }
        $qb->orderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Tags without group ordered by name.
     *
     * @return Tag[]
     */
    public function findUngrouped(?Project $project = null): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.tagGroup IS NULL');
        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }
        $qb->orderBy('t.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * First N tags (by name) of each group, for preview in group overview.
     *
     * @return array<int, Tag[]> keyed by TagGroup id
     */
    public function findPreviewPerGroup(?Project $project = null, int $perGroup = 12): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.tagGroup IS NOT NULL');
        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }
        $qb->orderBy('t.name', 'ASC');

        $result = [];
        /** @var Tag $tag */
        foreach ($qb->getQuery()->getResult() as $tag) {
            $groupId = $tag->getGroupId();
            if (count($result[$groupId] ?? []) < $perGroup) {
                $result[$groupId][] = $tag;
            }
        }

        return $result;
    }

    /**
     * @return array<int, int> tag count keyed by TagGroup id
     */
    public function countPerGroup(?Project $project = null): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('IDENTITY(t.tagGroup) AS group_id, COUNT(t.id) AS cnt');
        $qb->where('t.tagGroup IS NOT NULL');
        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }
        $qb->groupBy('t.tagGroup');

        $counts = [];
        foreach ($qb->getQuery()->getResult() as $row) {
            $counts[(int) $row['group_id']] = (int) $row['cnt'];
        }

        return $counts;
    }

    public function countUngrouped(?Project $project = null): int
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(t.id)');
        $qb->where('t.tagGroup IS NULL');
        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countAll(?Project $project = null): int
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(t.id)');
        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Number of posts and pages using each tag.
     *
     * @param int[] $tagIds
     * @return array<int, array{posts: int, pages: int}> keyed by tag id
     */
    public function countUsage(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }

        $usage = [];
        foreach ($tagIds as $tagId) {
            $usage[$tagId] = ['posts' => 0, 'pages' => 0];
        }

        foreach ([PostTag::class => 'posts', PageTag::class => 'pages'] as $class => $key) {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('x.tagId AS tag_id, COUNT(x.tagId) AS cnt');
            /** @noinspection PhpDqlBuilderUnknownModelInspection */
            $qb->from($class, 'x');
            $qb->where('x.tagId IN (:ids)');
            $qb->setParameter('ids', $tagIds);
            $qb->groupBy('x.tagId');

            foreach ($qb->getQuery()->getResult() as $row) {
                $usage[(int) $row['tag_id']][$key] = (int) $row['cnt'];
            }
        }

        return $usage;
    }

    /**
     * Bulk assign tags to group, null removes them from any group.
     *
     * @param int[] $tagIds
     * @return int number of updated tags
     */
    public function setGroupForIds(array $tagIds, ?TagGroup $group, ?Project $project = null): int
    {
        if (empty($tagIds)) {
            return 0;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->update(Tag::class, 't');
        $qb->set('t.tagGroup', ':group');
        $qb->setParameter('group', $group);
        $qb->where('t.id IN (:ids)');
        $qb->setParameter('ids', $tagIds);
        if ($project !== null) {
            $qb->andWhere('t.project = :project');
            $qb->setParameter('project', $project);
        }

        return (int) $qb->getQuery()->execute();
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
