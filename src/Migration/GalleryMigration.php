<?php

namespace Sovic\Cms\Migration;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Sovic\Cms\Entity\Gallery;
use Sovic\Cms\Entity\GalleryItem;
use Sovic\Cms\Repository\GalleryItemRepository;

class GalleryMigration extends AbstractMigration
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @throws Exception
     */
    public function migrate(array $options = []): void
    {
        if (empty($options['cover_image_gallery_names'])) {
            $options['cover_image_gallery_names'] = ['page', 'post'];
        }

        $galleries = $this->entityManager->getRepository(Gallery::class)->findBy(
            [
                'name' => $options['cover_image_gallery_names'],
            ]
        );
        foreach ($galleries as $entity) {
            $gallery = new \Sovic\Cms\Gallery\Gallery($entity);
            $gallery->setEntityManager($this->entityManager);

            // set default cover image
            $gallery->setDefaultCoverImage();
        }

        /** @var GalleryItemRepository $galleryItemRepo */
        $galleryItemRepo = $this->entityManager->getRepository(GalleryItem::class);

        $galleries = $this->entityManager->getRepository(Gallery::class)->findAll();
        foreach ($galleries as $entity) {
            $gallery = new \Sovic\Cms\Gallery\Gallery($entity);
            $gallery->setEntityManager($this->entityManager);

            // fix empty model_id items
            $items = $galleryItemRepo->findBy([
                'galleryId' => $gallery->getId(),
                'modelId' => 0,
            ]);
            foreach ($items as $item) {
                $item->setModel($gallery->getEntity()->getModel());
                $item->setModelId($gallery->getEntity()->getModelId());
                $this->entityManager->persist($item);
            }
        }
        $this->entityManager->flush();

        // update timestamp -> update_date
        $rc = new ReflectionClass(GalleryItem::class);
        if ($rc->hasMethod('getTimestamp')) {
            $sql = '
                UPDATE gallery_item
                SET `create_date` = FROM_UNIXTIME(`timestamp`)
                WHERE `timestamp` IS NOT NULL
            ';
            $em = $this->entityManager;
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->executeQuery();
        }
    }
}
