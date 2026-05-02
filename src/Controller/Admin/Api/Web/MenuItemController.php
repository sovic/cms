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
