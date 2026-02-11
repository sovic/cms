<?php

namespace Sovic\Cms\Menu;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\MenuItem;
use Sovic\Cms\Repository\MenuItemRepository;
use Sovic\Common\Cacheable\AbstractCacheableData;
use Symfony\Contracts\Cache\CacheInterface;

class MenuCacheableData extends AbstractCacheableData
{
    public function __construct(
        CacheInterface                          $cache,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct($cache);
    }

    protected function getCacheKey(): string
    {
        return 'cms_menu_tree';
    }

    protected function getExpiresAfer(): ?int
    {
        return 3600;
    }

    /**
     * @return array<string, array> keyed by position
     */
    protected function getFreshData(): array
    {
        /** @var MenuItemRepository $repo */
        $repo = $this->entityManager->getRepository(MenuItem::class);
        $items = $repo->findPublishedOrdered();

        return $this->buildPositionTrees($items);
    }

    /**
     * @param MenuItem[] $items
     * @return array<string, array> keyed by position
     */
    private function buildPositionTrees(array $items): array
    {
        // group all items by parent_id
        $grouped = [];
        foreach ($items as $item) {
            $parentId = $item->getParentId() ?? 0;
            $grouped[$parentId][] = $item;
        }

        // build one tree per position, using first root item for each position
        $trees = [];
        $seenPositions = [];
        foreach ($grouped[0] ?? [] as $rootItem) {
            $position = $rootItem->getPosition();
            if ($position === null || isset($seenPositions[$position])) {
                continue;
            }
            $seenPositions[$position] = true;
            $trees[$position] = $this->buildSubtree($grouped, $rootItem->getId());
        }

        return $trees;
    }

    /**
     * @param array<int, MenuItem[]> $grouped
     */
    private function buildSubtree(array $grouped, int $parentId): array
    {
        $tree = [];
        foreach ($grouped[$parentId] ?? [] as $item) {
            $tree[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'url' => $item->getResolveUrl(),
                'classes' => $item->getClasses(),
                'visibility' => $item->getVisibility(),
                'children' => $this->buildSubtree($grouped, $item->getId()),
            ];
        }

        return $tree;
    }
}
