<?php

namespace Sovic\Cms\Controller;

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Controller\Trait\PostsControllerTrait;
use Sovic\Cms\Controller\Trait\ProjectControllerTrait;
use Sovic\Cms\Entity\Post;
use Sovic\Common\Controller\Trait\DownloadTrait;
use Sovic\Cms\Entity\GalleryItem;
use Sovic\Cms\Gallery\GalleryFactory;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends BaseController implements ProjectControllerInterface
{
    use DownloadTrait;
    use PostsControllerTrait;
    use ProjectControllerTrait;

    #[Route('/gallery/{id}/download', name: 'gallery:download', defaults: [])]
    public function galleryDownload(
        string                 $id,
        EntityManagerInterface $em,
        FilesystemOperator     $galleryStorage,
        GalleryFactory         $galleryFactory,
    ): Response {
        $gallery = $galleryFactory->loadById($id);
        if (!$gallery || !$gallery->entity->isDownloadEnabled()) {
            return $this->renderProject404();
        }

        $model = $gallery->entity->getModel();
        /** @noinspection DegradedSwitchInspection */
        switch ($model) {
            case 'post':
                $post = $em
                    ->getRepository(Post::class)
                    ->find($gallery->entity->getModelId());
                if (null === $post) {
                    return $this->renderProject404();
                }
                $fileName = $post->getName();
                break;

            default:
                return $this->renderProject404();
        }

        $gallery->setFilesystemOperator($galleryStorage);

        $slugify = new Slugify();
        $slugify->activateRuleSet('default');
        $fileName = ($slugify->slugify($fileName) ?: 'gallery') . '.zip';

        $response = new StreamedResponse(static function () use ($gallery): void {
            set_time_limit(0);
            $output = fopen('php://output', 'wb');
            $gallery->writeZipArchive($output);
            fclose($output);
        });
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $fileName,
        ));
        $response->headers->set('X-Accel-Buffering', 'no'); // stream through nginx without buffering
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }

    #[Route('/gallery/item/{id}/download', name: 'gallery:item-download', defaults: [])]
    public function galleryItemDownload(
        int                    $id,
        EntityManagerInterface $em,
        GalleryFactory         $galleryFactory,
        FilesystemOperator     $galleryStorage,
    ): void {
        $gallery = $galleryFactory->loadByGalleryItemId($id);
        if (!$gallery) {
            throw new InvalidArgumentException('Gallery not found with gallery item ID: ' . $id);
        }

        $galleryItem = $em->getRepository(GalleryItem::class)->find($id);
        if (!$galleryItem) {
            throw new InvalidArgumentException('Gallery item not found with ID: ' . $id);
        }

        $fileName = $galleryItem->getName() . '.' . $galleryItem->getExtension();

        try {
            $fileSize = $galleryStorage->fileSize($galleryItem->getPath());
            $stream = $galleryStorage->readStream($galleryItem->getPath());
        } catch (FilesystemException $e) {
            throw new InvalidArgumentException('Unable to read file', 0, $e);
        }

        $this->download($stream, $fileName, $fileSize);
    }
}
