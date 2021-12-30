<?php

namespace SovicCms\Gallery;

use SovicCms\Entity\Gallery;
use SovicCms\Entity\GalleryItem;
use SovicCms\Helpers\File;
use SovicCms\Helpers\Text;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

final class GalleryManager
{
    private EntityManagerInterface $entityManager;
    private QueryBuilder $queryBuilder;
    private string $modelName;

    public function __construct(EntityManagerInterface $entityManager, string $modelName)
    {
        $this->entityManager = $entityManager;
        $this->modelName = $modelName;
    }

    /**
     * @param null|int|array|int[] $modelId
     * @param string|null $galleryName
     * @return QueryBuilder
     */
    private function initQueryBuilder($modelId = null, string $galleryName = null): QueryBuilder
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('gi')->from(Gallery::class, 'g')->where('g.model = :model');
        $qb->leftJoin(GalleryItem::class, 'gi', Join::WITH, 'gi.galleryId = g.id');
        $qb->setParameter(':model', $this->modelName);
        $qb->andWhere('gi.temp = 0');
        if (null !== $modelId) {
            if (is_array($modelId)) {
                $qb->andWhere($qb->expr()->in('g.modelId', ':model_id'));
                $qb->setParameter(':model_id', $modelId, Connection::PARAM_INT_ARRAY);
            } else {
                $qb->andWhere('g.modelId = :model_id');
                $qb->setParameter(':model_id', $modelId);
            }
        }
        if (null !== $galleryName) {
            $qb->andWhere('g.name = :gallery_name');
            $qb->setParameter(':gallery_name', $galleryName);
        }
        $this->queryBuilder = $qb;

        return $qb;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getItems(int $limit = 0, int $offset = 0): array
    {
        $qb = $this->queryBuilder;
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        if ($offset) {
            $qb->setFirstResult($offset);
        }
        $items = $qb->getQuery()->getResult();

        $results = [];
        /** @var GalleryItem $item */
        foreach ($items as $item) {
            if (null === $item) {
                continue;
            }
            $result = [
                'id' => $item->getId(),
                'model' => $item->getModel(),
                'model_id' => $item->getModelId(),
                'hero' => $item->isHero(),
                'hero_mobile' => $item->isHeroMobile(),
                'width' => !empty($item->getWidth()) ? $item->getWidth() : null,
                'height' => !empty($item->getHeight()) ? $item->getHeight() : null,
            ];
            $mediumPaths = GalleryHelper::getMediumPaths($item, GalleryHelper::SIZES_SET_ALL);
            $results[] = array_merge($result, $mediumPaths);
        }

        return $results;
    }

    private function getSingleItem(): ?array
    {
        $items = $this->getItems(1);

        return !empty($items) ? $items[0] : null;
    }

    public function getTitlePhoto(int $modelId, string $galleryName): ?array
    {
        $qb = $this->initQueryBuilder($modelId, $galleryName);
        $qb->orderBy('gi.title', 'DESC');

        return $this->getSingleItem();
    }

    public function getTitlePhotos(array $modelIds, string $galleryName): ?array
    {
        $qb = $this->initQueryBuilder($modelIds, $galleryName);
        $qb->orderBy('gi.title', 'DESC');

        $result = $this->getItems();
        $return = [];
        foreach ($result as $item) {
            if (empty($return[$item['model_id']]) && !empty($item['model_id'])) {
                $return[$item['model_id']] = $item;
            }
        }

        return $return;
    }

    public function getHeroImages(int $modelId, string $galleryName): array
    {
        $qb = $this->initQueryBuilder($modelId, $galleryName);
        $qb->andWhere($qb->expr()->orX('gi.hero = 1', 'gi.heroMobile = 1'));

        $return = [
            'hero' => null,
            'hero_mobile' => null,
        ];
        foreach ($this->getItems() as $item) {
            if ($item['hero']) {
                $return['hero'] = $item;
            }
            if ($item['hero_mobile']) {
                $return['hero_mobile'] = $item;
            }
        }

        return $return;
    }

    public function getVideo(int $modelId): ?array
    {
        $expr = $this->entityManager->getExpressionBuilder();
        $qb = $this->initQueryBuilder($modelId, 'video');
        $qb->andWhere(
            $qb->expr()->orX(
                $expr->isNotNull('gi.name'),
                $expr->isNotNull('gi.description')
            )
        );
        $qb->orderBy('gi.sequence', 'ASC');
        /** @var GalleryItem $item */
        $items = $qb->getQuery()->getResult();
        if (count($items) <= 0) {
            return null;
        }
        $item = $items[0];
        if ($item->getDescription() && Text::isUrl($item->getDescription())) {
            $url = $item->getDescription();
        } elseif ($item->getName() || $item->getDescription()) {
            $filename = $item->getName() ?: $item->getDescription();
            $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getFile();
        } else {
            return null;
        }

        return [
            'url' => $url,
        ];
    }

    public function getGallery(int $modelId, string $galleryName): array
    {
        $qb = $this->initQueryBuilder($modelId, $galleryName);
        $qb->orderBy('gi.sequence', 'ASC');

        return $this->getItems();
    }

    public function getDocs(int $modelId): array
    {
        $expr = $this->entityManager->getExpressionBuilder();
        $qb = $this->initQueryBuilder($modelId, 'reading');
        $qb->andWhere(
            $qb->expr()->orX(
                $expr->isNotNull('gi.name'),
                $expr->isNotNull('gi.description')
            )
        );
        $qb->orderBy('gi.sequence', 'ASC');
        $items = $qb->getQuery()->getResult();
        $result = [];
        /** @var GalleryItem $item */
        foreach ($items as $item) {
            if ($item->getDescription() && Text::isUrl($item->getDescription())) {
                $name = File::publicFileName(basename($item->getFile()));
                $url = $item->getDescription();
            } elseif ($item->getName() || $item->getDescription()) {
                $filename = !empty($item->getName()) ? $item->getName() : $item->getDescription();
                $name = File::publicFileName($filename, $item->getFile());
                $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getFile();
            } else {
                continue;
            }

            $result[] = [
                'name' => $name,
                'url' => $url,
            ];
        }

        return $result;
    }
}
