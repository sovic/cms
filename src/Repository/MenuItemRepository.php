<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\MenuItem;

/**
 * @method MenuItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuItem[]    findAll()
 * @method MenuItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuItemRepository extends EntityRepository
{
    /**
     * @return MenuItem[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy(
            [],
            ['sequence' => 'ASC', 'id' => 'ASC'],
        );
    }

    /**
     * @return MenuItem[]
     */
    public function findPublishedOrdered(): array
    {
        return $this->findBy(
            ['isPublic' => true],
            ['sequence' => 'ASC', 'id' => 'ASC'],
        );
    }

    /**
     * @return MenuItem[]
     */
    public function findRootWithPosition(): array
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where('m.position IS NOT NULL');
        $qb->andWhere('m.parentId IS NULL');
        $qb->addOrderBy('m.name', 'ASC');
        $qb->addOrderBy('m.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return MenuItem[]
     */
    public function findByPosition(string $position): array
    {
        return $this->findBy(
            ['position' => $position],
            ['sequence' => 'ASC', 'id' => 'ASC'],
        );
    }

    /**
     * Build a hierarchical tree structure from a flat list.
     *
     * @param MenuItem[] $items
     * @return array<int, array{item: MenuItem, children: array}>
     */
    public function buildTree(array $items): array
    {
        $grouped = [];
        foreach ($items as $item) {
            $parentId = $item->getParentId() ?? 0;
            $grouped[$parentId][] = $item;
        }

        return $this->buildSubtree($grouped, 0);
    }

    /**
     * Build a tree for a specific position (root items with a given position and all their descendants).
     *
     * @param MenuItem[] $items
     * @return array<int, array{item: MenuItem, children: array}>
     */
    public function buildTreeForPosition(array $items, string $position): array
    {
        $grouped = [];
        foreach ($items as $item) {
            $parentId = $item->getParentId() ?? 0;
            $grouped[$parentId][] = $item;
        }

        // find root items with the given position
        $roots = [];
        foreach ($grouped[0] ?? [] as $item) {
            if ($item->getPosition() === $position) {
                $roots[] = [
                    'item' => $item,
                    'children' => $this->buildSubtree($grouped, $item->getId()),
                ];
            }
        }

        return $roots;
    }

    /**
     * @param array<int, MenuItem[]> $grouped
     * @return array<int, array{item: MenuItem, children: array}>
     */
    private function buildSubtree(array $grouped, int $parentId): array
    {
        $tree = [];
        foreach ($grouped[$parentId] ?? [] as $item) {
            $tree[] = [
                'item' => $item,
                'children' => $this->buildSubtree($grouped, $item->getId()),
            ];
        }

        return $tree;
    }
}
