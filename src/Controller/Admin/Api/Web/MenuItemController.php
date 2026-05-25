<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\MenuItem;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MenuItemController extends AbstractBaseApiController
{
    use JsonResponseTrait;

    private const ToggleableFields = [
        'is_public',
    ];

    #[Route(
        '/admin/api/web/menu-item/{id}/move',
        name: 'admin:api:web:menu-item:move',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function moveItem(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $menuItem = $em->getRepository(MenuItem::class)->find($id);
        if ($menuItem === null) {
            return $this->sendFail(404);
        }

        $data = $this->getRequestData($request);
        $direction = $data['direction'] ?? null;
        if (!in_array($direction, ['up', 'down'], true)) {
            $this->addError('invalid_direction');

            return $this->sendFail();
        }

        $siblings = $em->getRepository(MenuItem::class)->findBy(
            ['parentId' => $menuItem->getParentId()],
            ['sequence' => 'ASC', 'id' => 'ASC'],
        );

        $currentIndex = null;
        foreach ($siblings as $index => $sibling) {
            if ($sibling->getId() === $menuItem->getId()) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex === null) {
            return $this->sendFail();
        }

        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;
        if (!isset($siblings[$swapIndex])) {
            return $this->sendSuccess();
        }

        [$siblings[$currentIndex], $siblings[$swapIndex]] = [$siblings[$swapIndex], $siblings[$currentIndex]];

        $seq = 1;
        foreach ($siblings as $sibling) {
            $sibling->setSequence($seq++);
            $em->persist($sibling);
        }
        $em->flush();

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/menu-item/{id}/toggle-state',
        name: 'admin:api:web:menu-item:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $menuItem = $em->getRepository(MenuItem::class)->find($id);
        if ($menuItem === null) {
            return $this->sendFail(404);
        }

        $data = $this->getRequestData($request);
        $field = $data['field'] ?? null;
        if (!in_array($field, self::ToggleableFields, true)) {
            $this->addError('invalid_field');

            return $this->sendFail();
        }

        $state = $data['state'] ?? null;

        if ($field === 'is_public') {
            $menuItem->setIsPublic($state);
            $em->flush();
            $this->data['value'] = $menuItem->isPublic();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }
}
