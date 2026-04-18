<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use ImagickException;
use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Cms\Gallery\GalleryItemResultSet;
use Sovic\Cms\Gallery\GalleryManager;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UploadController extends AdminBaseController
{
    use JsonResponseTrait;

    private string $galleryBaseUrl;

    public function __construct(
        EntityManagerInterface                   $entityManager,
        #[Autowire('%gallery_base_url%')] string $galleryBaseUrl,
    ) {
        parent::__construct($entityManager);
        $this->galleryBaseUrl = $galleryBaseUrl;
    }

    #[Route(
        '/admin/api/upload',
        name: 'admin:api:upload',
        methods: ['POST'],
    )]
    public function upload(
        Request            $request,
        FilesystemOperator $galleryStorage,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $model = $request->request->get('model');
        $modelId = (int) $request->request->get('model_id');
        $galleryName = $request->request->get('gallery_name');
        $file = $request->files->get('file');

        if (!$model || !$modelId || !$galleryName || !$file) {
            $this->data['message'] = 'Missing required parameters.';

            return $this->sendFail();
        }

        $manager = new GalleryManager($model, $modelId);
        $manager->setEntityManager($this->entityManager);
        $manager->setFilesystemOperator($galleryStorage);

        try {
            $gallery = $manager->loadGallery($galleryName);
            $item = $gallery->uploadFile($file);
        } catch (InvalidArgumentException $e) {
            $this->data['message'] = $e->getMessage();

            return $this->sendFail();
        } catch (FilesystemException|ImagickException $e) {
            $this->data['message'] = 'Upload failed: ' . $e->getMessage();

            return $this->sendFail(500);
        }

        $resultSet = new GalleryItemResultSet([$item]);
        $resultSet->setBaseUrl($this->galleryBaseUrl);
        $items = $resultSet->toArray();
        $this->data['item'] = $items[0] ?? null;

        return $this->sendSuccess();
    }
}
