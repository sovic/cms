<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Sovic\Cms\Entity\Gallery;
use Sovic\Cms\Entity\GalleryItem;

/**
 * @method GalleryItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method GalleryItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method GalleryItem[]    findAll()
 * @method GalleryItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GalleryItemRepository extends EntityRepository
{
    private function getGalleryQueryBuilder(Gallery $gallery): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('gi')
            ->from(Gallery::class, 'g')
            ->where('g.model = :model');
        $qb->leftJoin(GalleryItem::class, 'gi', Join::WITH, 'gi.galleryId = g.id');

        $qb->setParameter(':model', $gallery->getModel());
        $qb->andWhere('g.modelId = :model_id');
        $qb->setParameter(':model_id', $gallery->getModelId());
        $qb->andWhere('g.name = :gallery_name');
        $qb->setParameter(':gallery_name', $gallery->getName());

        $qb->andWhere('gi.isTemp = 0');

        return $qb;
    }

    private function getBatchQueryBuilder(string $model, array $modelIds, string $galleryName): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('gi')
            ->from(Gallery::class, 'g')
            ->where('g.model = :model');
        $qb->leftJoin(GalleryItem::class, 'gi', Join::WITH, 'gi.galleryId = g.id');

        $qb->andWhere('g.model = :model');
        $qb->setParameter(':model', $model);
        $qb->andWhere('g.modelId IN (:model_id)');
        $qb->setParameter(':model_id', $modelIds);
        $qb->andWhere('g.name = :gallery_name');
        $qb->setParameter(':gallery_name', $galleryName);

        $qb->andWhere('gi.isTemp = 0');

        return $qb;
    }

    public function findByGallery(Gallery $gallery, ?int $offset = null, ?int $limit = null): array
    {
        $qb = $this->getGalleryQueryBuilder($gallery);
        $qb->addOrderBy('gi.sequence', 'ASC');
        if ($offset) {
            $qb->setFirstResult($offset);
        }
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByGallery(Gallery $gallery): int
    {
        $qb = $this->getGalleryQueryBuilder($gallery);
        $qb->select('COUNT(gi.id)');

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException|NonUniqueResultException) {
            // TODO log

            return 0;
        }
    }

    public function findGalleryCoverImage(Gallery $gallery): ?GalleryItem
    {
        $qb = $this->getGalleryQueryBuilder($gallery);
        $qb->andWhere('gi.isCover = 1');
        $qb->orderBy('gi.id', 'DESC');
        $qb->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (Exception) {
        }

        return null;
    }

    public function findGalleriesCovers(string $model, array $modelIds, string $galleryName): array
    {
        $qb = $this->getBatchQueryBuilder($model, $modelIds, $galleryName);
        $qb->andWhere('gi.isCover = 1');

        return $qb->getQuery()->getResult();
    }

    public function findGalleryHeroImage(Gallery $gallery): ?GalleryItem
    {
        $qb = $this->getGalleryQueryBuilder($gallery);
        // TODO single item, multiple variants
        $qb->andWhere($qb->expr()->orX('gi.isHero = 1', 'gi.isHeroMobile = 1'));
        $qb->setMaxResults(1);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (Exception) {
        }

        return null;
    }
}
