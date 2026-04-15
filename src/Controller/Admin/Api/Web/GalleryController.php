<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Sovic\Gallery\Entity\Gallery as GalleryEntity;
use Sovic\Gallery\Entity\GalleryItem;
use Sovic\Gallery\Gallery\GalleryFactory;
use Sovic\Gallery\Repository\GalleryItemRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class GalleryController extends AdminBaseController
{
    use JsonResponseTrait;

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
        string             $model,
        int                $modelId,
        int                $itemId,
        GalleryFactory     $galleryFactory,
        FilesystemOperator $galleryStorage,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $item = $this->loadItem($model, $modelId, $itemId);
        if ($item === null) {
            $this->data['message'] = 'Gallery item not found.';

            return $this->sendFail(404);
        }

        $gallery = $galleryFactory->loadByGalleryItemId($itemId);
        if ($gallery === null) {
            $this->data['message'] = 'Gallery not found.';

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
    public function moveGalleryItemLeft(string $model, int $modelId, int $itemId): JsonResponse
    {
        return $this->moveGalleryItem($model, $modelId, $itemId, -1);
    }

    #[Route(
        '/admin/api/{model}/{modelId}/gallery/{galleryName}/item/{itemId}/move-right',
        name: 'admin:api:gallery:item:move-right',
        requirements: ['model' => '[a-z]+', 'modelId' => '\d+', 'itemId' => '\d+'],
        methods: ['POST'],
    )]
    public function moveGalleryItemRight(string $model, int $modelId, int $itemId): JsonResponse
    {
        return $this->moveGalleryItem($model, $modelId, $itemId, +1);
    }

    private function moveGalleryItem(string $model, int $modelId, int $itemId, int $direction): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var GalleryItemRepository $repo */
        $repo = $this->entityManager->getRepository(GalleryItem::class);

        $item = $this->loadItem($model, $modelId, $itemId);
        if ($item === null) {
            $this->data['message'] = 'Gallery item not found.';

            return $this->sendFail(404);
        }

        $galleryEntity = $this->entityManager->getRepository(GalleryEntity::class)->find($item->getGalleryId());
        if ($galleryEntity === null) {
            $this->data['message'] = 'Gallery not found.';

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
            $this->data['message'] = 'Item is already at the edge.';

            return $this->sendFail();
        }

        $neighbor = $orderedItems[$neighborIndex];
        $item->setSequence($neighborIndex);
        $neighbor->setSequence($currentIndex);

        $this->entityManager->flush();

        return $this->sendSuccess();
    }
}
