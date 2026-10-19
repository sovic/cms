<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Common\Controller\Trait\JsonRequestTrait;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Sovic\Cms\Entity\Gallery as GalleryEntity;
use Sovic\Cms\Entity\GalleryItem;
use Sovic\Cms\Gallery\GalleryFactory;
use Sovic\Cms\Gallery\GalleryManager;
use Sovic\Cms\Repository\GalleryItemRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GalleryController extends AdminBaseController
{
    use JsonRequestTrait;
    use JsonResponseTrait;

    private const ToggleableFields = [
        'is_download_enabled',
    ];

    private function loadItem(string $model, int $modelId, int $itemId): ?GalleryItem
    {
        /** @var GalleryItemRepository $repo */
        $repo = $this->entityManager->getRepository(GalleryItem::class);

        return $repo->findOneBy([
            'id' => $itemId,
            'model' => $model,
            'modelId' => $modelId,
        ]);
    }

    #[Route(
        '/admin/api/{model}/{modelId}/gallery/{galleryName}/toggle-state',
        name: 'admin:api:gallery:toggle-state',
        requirements: ['model' => '[a-z]+', 'modelId' => '\d+', 'galleryName' => '[a-z]+'],
        methods: ['POST'],
    )]
    public function toggleState(
        string  $model,
        int     $modelId,
        string  $galleryName,
        Request $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $this->getRequestData($request);
        $field = $data['field'] ?? null;
        if (!in_array($field, self::ToggleableFields, true)) {
            $this->addError('invalid_field');

            return $this->sendFail();
        }

        $manager = new GalleryManager($model, $modelId);
        $manager->setEntityManager($this->entityManager);
        try {
            $gallery = $manager->loadGallery($galleryName);
        } catch (InvalidArgumentException $e) {
            $this->data['message'] = $e->getMessage();

            return $this->sendFail();
        }

        $state = (bool) ($data['state'] ?? false);
        $entity = $gallery->getEntity();

        if ($field === 'is_download_enabled') {
            $entity->setIsDownloadEnabled($state);
            $this->entityManager->flush();

            $this->data['value'] = $entity->isDownloadEnabled();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }

    /**
     * @throws FilesystemException
     */
    #[Route(
        '/admin/api/{model}/{modelId}/gallery/{galleryName}/item/{itemId}/delete',
        name: 'admin:api:gallery:item:delete',
        requirements: ['model' => '[a-z]+', 'modelId' => '\d+', 'itemId' => '\d+'],
        methods: ['POST'],
    )]
    public function deleteGalleryItem(
        string              $model,
        int                 $modelId,
        int                 $itemId,
        GalleryFactory      $galleryFactory,
        FilesystemOperator  $galleryStorage,
        TranslatorInterface $t,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $item = $this->loadItem($model, $modelId, $itemId);
        if ($item === null) {
            $this->data['message'] = $t->trans('api.item_not_found', domain: 'gallery');

            return $this->sendFail(404);
        }

        $gallery = $galleryFactory->loadByGalleryItemId($itemId);
        if ($gallery === null) {
            $this->data['message'] = $t->trans('api.not_found', domain: 'gallery');

            return $this->sendFail(404);
        }

        $gallery->setFilesystemOperator($galleryStorage);
        $gallery->deleteItem($itemId);

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/{model}/{modelId}/gallery/{galleryName}/item/{itemId}/set-cover',
        name: 'admin:api:gallery:item:set-cover',
        requirements: ['model' => '[a-z]+', 'modelId' => '\d+', 'itemId' => '\d+'],
        methods: ['POST'],
    )]
    public function setGalleryItemCover(string $model, int $modelId, int $itemId): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var GalleryItemRepository $repo */
        $repo = $this->entityManager->getRepository(GalleryItem::class);

        $item = $this->loadItem($model, $modelId, $itemId);
        if ($item === null) {
            $this->data['message'] = 'Gallery item not found.';

            return $this->sendFail(404);
        }

        $existingCovers = $repo->findBy([
            'galleryId' => $item->getGalleryId(),
            'isCover' => true,
        ]);
        foreach ($existingCovers as $cover) {
            $cover->setIsCover(false);
            $this->entityManager->persist($cover);
        }

        $item->setIsCover(true);
        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/{model}/{modelId}/gallery/{galleryName}/item/{itemId}/move-left',
        name: 'admin:api:gallery:item:move-left',
        requirements: ['model' => '[a-z]+', 'modelId' => '\d+', 'itemId' => '\d+'],
        methods: ['POST'],
    )]
    public function moveGalleryItemLeft(string $model, int $modelId, int $itemId, TranslatorInterface $t): JsonResponse
    {
        return $this->moveGalleryItem($model, $modelId, $itemId, -1, $t);
    }

    #[Route(
        '/admin/api/{model}/{modelId}/gallery/{galleryName}/item/{itemId}/move-right',
        name: 'admin:api:gallery:item:move-right',
        requirements: ['model' => '[a-z]+', 'modelId' => '\d+', 'itemId' => '\d+'],
        methods: ['POST'],
    )]
    public function moveGalleryItemRight(string $model, int $modelId, int $itemId, TranslatorInterface $t): JsonResponse
    {
        return $this->moveGalleryItem($model, $modelId, $itemId, +1, $t);
    }

    private function moveGalleryItem(string $model, int $modelId, int $itemId, int $direction, TranslatorInterface $t): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var GalleryItemRepository $repo */
        $repo = $this->entityManager->getRepository(GalleryItem::class);

        $item = $this->loadItem($model, $modelId, $itemId);
        if ($item === null) {
            $this->data['message'] = $t->trans('api.item_not_found', domain: 'gallery');

            return $this->sendFail(404);
        }

        $galleryEntity = $this->entityManager->getRepository(GalleryEntity::class)->find($item->getGalleryId());
        if ($galleryEntity === null) {
            $this->data['message'] = $t->trans('api.not_found', domain: 'gallery');

            return $this->sendFail(404);
        }

        $orderedItems = $repo->findByGallery($galleryEntity);

        // Normalize sequences to eliminate any duplicates before swapping
        foreach ($orderedItems as $i => $orderedItem) {
            $orderedItem->setSequence($i);
        }

        $currentIndex = null;
        foreach ($orderedItems as $i => $orderedItem) {
            if ($orderedItem->getId() === $itemId) {
                $currentIndex = $i;
                break;
            }
        }

        $neighborIndex = $currentIndex + $direction;
        if ($currentIndex === null || !isset($orderedItems[$neighborIndex])) {
            $this->data['message'] = $t->trans('api.edge', domain: 'gallery');

            return $this->sendFail();
        }

        $neighbor = $orderedItems[$neighborIndex];
        $item->setSequence($neighborIndex);
        $neighbor->setSequence($currentIndex);

        $this->entityManager->flush();

        return $this->sendSuccess();
    }
}
