<?php

namespace Sovic\Cms\Migration;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Sovic\Gallery\Entity\Gallery;
use Sovic\Gallery\Entity\GalleryItem;
use Sovic\Gallery\Gallery\GalleryHelper;
use Sovic\Gallery\Migration\AbstractMigration;
use Sovic\Gallery\Repository\GalleryItemRepository;

class StorageMigration extends AbstractMigration
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FilesystemOperator     $galleryStorage,
    ) {
    }

    /**
     * @param array $options example
     * [
     *   'gallery' => Gallery|null, // move only selected gallery
     *   'variants' => ['full', 'thumb', 'small', 'hp', 'big', 'cms', 'post', 'cms_block'], // old variants
     *   'limit' => 10, // limit of galleries to move
     * ]
     * @throws FilesystemException
     */
    public function migrate(array $options = []): void
    {
        if (empty($options['variants'])) {
            $options['variants'] = ['full', 'thumb', 'small', 'hp', 'big', 'cms'];
        }
        if (empty($options['limit'])) {
            $options['limit'] = 100;
        }

        if (!empty($options['gallery'])) {
            if (!$options['gallery'] instanceof \Sovic\Gallery\Gallery\Gallery) {
                throw new InvalidArgumentException('Gallery must be instance of ' . Gallery::class);
            }
            $galleries = [$options['gallery']->getEntity()];
        } else {
            /** @var Gallery[] $galleries */
            $galleries = $this->entityManager->getRepository(Gallery::class)->findBy(
                [
                    'isProcessed' => false,
                ],
                [
                    'id' => 'ASC',
                ],
                $options['limit'],
            );
        }

        $filesystem = $this->galleryStorage;
        /** @var GalleryItemRepository $galleryItemRepo */
        $galleryItemRepo = $this->entityManager->getRepository(GalleryItem::class);

        foreach ($galleries as $entity) {
            $gallery = new \Sovic\Gallery\Gallery\Gallery($entity);
            $gallery->setEntityManager($this->entityManager);

            $newStoragePath = $gallery->getGalleryStoragePath();
            $filesystem->createDirectory($newStoragePath, [
                Config::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC,
            ]);

            $items = $galleryItemRepo->findByGallery($entity);
            foreach ($items as $item) {
                // skip already moved items
                if ($item->getPath() !== null) {
                    continue;
                }

                $paths = GalleryHelper::getMediaPaths($item, '', $options['variants']);
                foreach ($paths as $path) {
                    if ($filesystem->fileExists($path)) {
                        $newPath = $newStoragePath . '/' . basename($path);
                        $filesystem->move($path, $newPath);
                    }
                }

                $originalPath = $paths[GalleryHelper::SIZE_FULL];
                $item->setPath($newStoragePath . '/' . basename($originalPath));
                $this->entityManager->persist($item);
            }

            $entity->setIsProcessed(true);
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
