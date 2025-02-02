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
use Sovic\Gallery\Entity\GalleryItem;
use Sovic\Gallery\Gallery\GalleryFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends BaseController implements ProjectControllerInterface
{
    use DownloadTrait;
    use PostsControllerTrait;
    use ProjectControllerTrait;

    #[Route('/gallery/{id}/zip', name: 'gallery_download', defaults: [])]
    public function galleryZip(
        string                 $id,
        EntityManagerInterface $em,
        FilesystemOperator     $galleryStorage,
        GalleryFactory         $galleryFactory,
        Request                $request,
    ): Response {
        $gallery = $galleryFactory->loadById($id);
        if (!$gallery || !$gallery->entity->isDownloadEnabled()) {
            return $this->renderProject404();
        }

        // only post now
        if ($gallery->entity->getModel() !== 'post') {
            return $this->renderProject404();
        }

        /** @noinspection DegradedSwitchInspection */
        switch ($gallery->entity->getModel()) {
            case 'post':
                $post = $em
                    ->getRepository(Post::class)
                    ->find($gallery->entity->getModelId());
                if (null === $post) {
                    return $this->renderProject404();
                }
                $secret = $request->query->get('secret');
                if ($secret !== $post->getSecret()) {
                    return $this->renderProject404();
                }
                $fileName = $post->getName();
                break;

            default:
                return $this->renderProject404();
        }

        $gallery->setFilesystemOperator($galleryStorage);
        try {
            $archivePath = $gallery->createZipArchive();

            $slugify = new Slugify();
            $slugify->activateRuleSet('default');
            $fileName = $slugify->slugify($fileName) . '.zip';

            $this->downloadFile($archivePath, $fileName);
        } catch (FilesystemException) {
            return $this->renderProject404();
        }

        die();
    }

    #[Route('/gallery/item/{id}/download', name: 'gallery_item_download', defaults: [])]
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

        $projectDir = $this->getParameter('kernel.project_dir');
        $path = $projectDir . '/public/gallery/' . $galleryItem->getPath();

        $fileSize = filesize($path);
        $fileName = $galleryItem->getName() . '.' . $galleryItem->getExtension();

        try {
            $stream = $galleryStorage->readStream($galleryItem->getPath());
        } catch (FilesystemException $e) {
            throw new InvalidArgumentException('Unable to read file', 0, $e);
        }

        $this->download($stream, $fileName, $fileSize);
    }
}
