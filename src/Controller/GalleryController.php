<?php

namespace Sovic\Cms\Controller;

use Cocur\Slugify\Slugify;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Controller\Trait\PostsControllerTrait;
use Sovic\Cms\Controller\Trait\ProjectControllerTrait;
use Sovic\Common\Controller\Trait\DownloadTrait;
use Sovic\Gallery\Gallery\GalleryFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends BaseController
{
    use DownloadTrait;
    use PostsControllerTrait;
    use ProjectControllerTrait;

    #[Route('/gallery/{id}/zip', name: 'gallery_download', defaults: [])]
    public function galleryZip(
        string             $id,
        FilesystemOperator $galleryStorage,
        GalleryFactory     $galleryFactory,
        Request            $request,
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
                $post = $this->postFactory->loadById($gallery->entity->getModelId());
                if (null === $post) {
                    return $this->renderProject404();
                }
                $secret = $request->query->get('secret');
                if ($secret !== $post->entity->getSecret()) {
                    return $this->renderProject404();
                }
                break;

            default:
                return $this->renderProject404();
        }

        $gallery->setFilesystemOperator($galleryStorage);
        try {
            $archivePath = $gallery->createZipArchive();

            $slugify = new Slugify();
            $slugify->activateRuleSet('default');
            $fileName = $slugify->slugify($this->post->entity->getName()) . '.zip';

            $this->downloadFile($archivePath, $fileName);

        } catch (FilesystemException) {
            return $this->renderProject404();
        }

        die();
    }

    #[Route('/gallery/item/{id}/download', name: 'gallery_item_download', defaults: [])]
    public function galleryItemDownload(): void
    {
        die();
    }
}
