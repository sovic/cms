<?php

namespace Sovic\Cms\Gallery;

use DateTimeImmutable;
use Imagick;
use ImagickException;
use InvalidArgumentException;
use League\Flysystem\Config;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Visibility;
use Sovic\Cms\Entity\Gallery as GalleryEntity;
use Sovic\Cms\Entity\GalleryItem;
use Sovic\Cms\Repository\GalleryItemRepository;
use Sovic\Common\Model\AbstractEntityModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use ZipArchive;

/**
 * @property GalleryEntity $entity
 * @method GalleryEntity getEntity()
 */
class Gallery extends AbstractEntityModel
{
    private array $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private FilesystemOperator $filesystemOperator;

    public function __construct(GalleryEntity $entity)
    {
        $this->setEntity($entity);
    }

    public function setFilesystemOperator(FilesystemOperator $filesystemOperator): void
    {
        $this->filesystemOperator = $filesystemOperator;
    }

    public function getCoverImage(): ?array
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $cover = $repo->findGalleryCoverImage($this->getEntity());
        if (!$cover) {
            return null;
        }

        return (new GalleryItemResultSet([$cover]))->toArray()[0];
    }

    public function getGalleryStoragePath(): string
    {
        /** @noinspection SpellCheckingInspection */
        $hash = md5($this->getEntity()->getId() . 'T3zmR34Swh4FZAA'); // TODO config salt
        $path = str_split(substr($hash, 0, 6));
        $path[] = $hash;

        return implode(DIRECTORY_SEPARATOR, $path);
    }

    public function getHeroImage(): ?array
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $hero = $repo->findGalleryHeroImage($this->getEntity());
        if (!$hero) {
            return null;
        }

        return (new GalleryItemResultSet([$hero]))->toArray()[0];
    }

    public function getId(): int
    {
        return $this->getEntity()->getId();
    }

    public function getItemsCount(): int
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);

        return $repo->countByGallery($this->getEntity());
    }

    public function getItems(?int $offset = null, ?int $limit = null): array
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $items = $repo->findByGallery($this->getEntity(), $offset, $limit);

        return (new GalleryItemResultSet($items))->toArray();
    }

    public function getItemsResultSet(?int $offset = null, ?int $limit = null): GalleryItemResultSet
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $items = $repo->findByGallery($this->getEntity(), $offset, $limit);

        return (new GalleryItemResultSet($items));
    }
//
//    public function getVideo(): ?array
//    {
////        $expr = $this->entityManager->getExpressionBuilder();
////        $qb = $this->initQueryBuilder('video');
////        $qb->andWhere(
////            $qb->expr()->orX(
////                $expr->isNotNull('gi.name'),
////                $expr->isNotNull('gi.description')
////            )
////        );
////        $qb->orderBy('gi.sequence', 'ASC');
////        /** @var GalleryItem $item */
////        $items = $qb->getQuery()->getResult();
////        if (count($items) <= 0) {
////            return null;
////        }
////        $item = $items[0];
////        if ($item->getDescription() && Text::isUrl($item->getDescription())) {
////            $url = $item->getDescription();
////        } elseif ($item->getName() || $item->getDescription()) {
////            $filename = $item->getName() ?: $item->getDescription();
////            $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getExtension();
////        } else {
////            return null;
////        }
////
////        return [
////            'url' => $url,
////        ];
//
//        return null;
//    }
//
//    public function getDocs(): array
//    {
////        $expr = $this->entityManager->getExpressionBuilder();
////        $qb = $this->initQueryBuilder('reading');
////        $qb->andWhere(
////            $qb->expr()->orX(
////                $expr->isNotNull('gi.name'),
////                $expr->isNotNull('gi.description')
////            )
////        );
////        $qb->orderBy('gi.sequence', 'ASC');
////        $items = $qb->getQuery()->getResult();
////        $result = [];
////        /** @var GalleryItem $item */
////        foreach ($items as $item) {
////            $description = $item->getDescription();
////            if ($description && Text::isUrl($description)) {
////                $name = File::publicFileName(basename($item->getExtension()));
////                $url = $item->getDescription();
////            } elseif ($description || $item->getName()) {
////                $filename = !empty($item->getName()) ? $item->getName() : $description;
////                $name = File::publicFileName($filename, $item->getExtension());
////                $url = '/dl/' . $item->getId() . '/' . $filename . '.' . $item->getExtension();
////            } else {
////                continue;
////            }
////
////            $result[] = [
////                'name' => $name,
////                'url' => $url,
////            ];
////        }
////
////        return $result;
//        return [];
//    }
//
//    public function getDownloads(): array
//    {
////        $qb = $this->initQueryBuilder('downloads');
////        $qb->orderBy('gi.sequence', 'ASC');
////        $items = $qb->getQuery()->getResult();
////
////        $results = [];
////        /** @var GalleryItem $item */
////        foreach ($items as $item) {
////            $mediaPaths = GalleryHelper::getMediaPaths($item, $this->baseUrl, [GalleryHelper::SIZE_FULL]);
////            $result = [
////                'name' => File::publicFileName($item->getName(), $item->getExtension()),
////                'filename' => $item->getName() . '.' . $item->getExtension(),
////                'url' => $mediaPaths[GalleryHelper::SIZE_FULL],
////            ];
////            $results[] = $result;
////        }
////
////        return $results;
//        return [];
//    }


    public function setCoverImage(GalleryItem $item): void
    {
        $item->setIsCover(true);
        $this->getEntityManager()->persist($item);
        $this->getEntityManager()->flush();
    }

    public function setDefaultCoverImage(): void
    {
        /** @var GalleryItemRepository $galleryItemRepo */
        $galleryItemRepo = $this->getEntityManager()->getRepository(GalleryItem::class);

        $items = $galleryItemRepo->findBy([
            'galleryId' => $this->getId(),
            'isCover' => true,
        ]);
        if (count($items) === 0) {
            $defaultCover = $galleryItemRepo->findOneBy(
                [
                    'galleryId' => $this->getId(),
                    'extension' => 'jpg',
                ],
                [
                    'width' => 'DESC', // widest first
                    'id' => 'ASC',
                ]
            );
            if ($defaultCover) {
                $this->setCoverImage($defaultCover);
            }
        }
    }

    /**
     * @param string $path
     * @return GalleryItem[]
     * @throws FilesystemException
     * @throws ImagickException
     */
    public function uploadPath(string $path): array
    {
        $uploadedItems = [];
        if (is_file($path)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $filename = pathinfo($path, PATHINFO_FILENAME);
            $uploadedItems[] = $this->handleUpload($path, $extension, $filename);
        }
        if (is_dir($path)) {
            $files = array_diff(scandir($path), ['.', '..']);
            foreach ($files as $file) {
                $filePath = $path . DIRECTORY_SEPARATOR . $file;
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $filename = pathinfo($filePath, PATHINFO_FILENAME);
                $uploadedItems[] = $this->handleUpload($filePath, $extension, $filename);
            }
        }

        return $uploadedItems;
    }

    /**
     * @throws FilesystemException
     * @throws ImagickException
     */
    public function uploadFile(UploadedFile $file): GalleryItem
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return $this->handleUpload($file->getPathname(), $extension, $filename);
    }

    /**
     * @throws ImagickException
     * @throws FilesystemException
     */
    private function handleUpload(string $path, string $extension, string $filename): GalleryItem
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException('invalid path');
        }

        $item = new GalleryItem();
        $item->setGallery($this->getEntity());
        $item->setExtension($extension);
        $item->setName($filename);
        $item->setModel($this->getEntity()->getModel());
        $item->setModelId($this->getEntity()->getModelId());

        // image processing
        if (in_array($extension, $this->imageExtensions, true)) {
            $image = new Imagick($path);
            $width = $image->getImageWidth();
            $height = $image->getImageHeight();
            $item->setWidth($width);
            $item->setHeight($height);
        }

        $item->setSequence($this->getItemsCount() + 1);
        $item->setCreateDate(new DateTimeImmutable());
        $item->setIsTemp(true);
        if ($this->getCoverImage() === null) {
            $item->setIsCover(true);
        }

        $em = $this->getEntityManager();
        $em->persist($item);
        $em->flush();

        $fileSystemFilename = $item->getId() . ($extension ? '.' . $extension : '');
        $storagePath = $this->getGalleryStoragePath();
        $fileSystemPath = $storagePath . DIRECTORY_SEPARATOR . $fileSystemFilename;

        $filesystem = $this->filesystemOperator;
        $filesystem->createDirectory($storagePath, [
            Config::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC,
        ]);
        $filesystem->write($fileSystemPath, file_get_contents($path));

        $item->setPath($fileSystemPath);
        $item->setIsTemp(false);
        $em->persist($item);
        $em->flush();

        return $item;
    }

    /**
     * @throws FilesystemException
     */
    public function createZipArchive(?string $zipPath = null): string
    {
        if (!$this->getEntity()->isDownloadEnabled()) {
            throw new InvalidArgumentException('Download is not enabled');
        }

        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $items = $repo->findByGallery($this->getEntity());

        $storagePath = $this->getGalleryStoragePath();

        $zip = new ZipArchive();
        if (!$zipPath) {
            $zipPath = tempnam(sys_get_temp_dir(), 'gallery');
        }

        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($items as $item) {
            $extension = $item->getExtension();
            $fileSystemFilename = $item->getId() . ($extension ? '.' . $extension : '');
            $fileSystemPath = $storagePath . DIRECTORY_SEPARATOR . $fileSystemFilename;
            $zip->addFromString(
                $item->getName() . ($extension ? '.' . $extension : ''),
                $this->filesystemOperator->read($fileSystemPath)
            );
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * @throws FilesystemException
     */
    public function delete(): void
    {
        $filesystem = $this->filesystemOperator;
        $filesystem->delete($this->getGalleryStoragePath());

        $em = $this->getEntityManager();
        $em->remove($this->getEntity());
        $em->flush();
    }

    /**
     * @throws FilesystemException
     */
    public function deleteItem(int $id): void
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->getEntityManager()->getRepository(GalleryItem::class);
        $item = $repo->findOneBy([
            'id' => $id,
            'model' => $this->getEntity()->getModel(),
            'modelId' => $this->getEntity()->getModelId(),
        ]);
        if (!$item) {
            // BC, remove after migration scripts added
            $item = $repo->find($id);
            if (!$item) {
                return;
            }
        }

        $filesystem = $this->filesystemOperator;
        $filesystem->delete($item->getPath());

        $em = $this->getEntityManager();
        $em->remove($item);
        $em->flush();
    }
}
