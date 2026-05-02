<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\AdminBaseController;
use Sovic\Cms\Entity\MenuItem;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class MenuItemController extends AdminBaseController
{
    use JsonResponseTrait;

    #[Route(
        '/admin/api/web/menu-item/{id}/toggle-state',
        name: 'admin:api:web:menu-item:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['GET', 'POST'],
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

        $field = $request->request->get('field');
        $state = (bool) $request->request->get('state');

        if ($field === 'is_public') {
            $menuItem->setIsPublic($state);
            $em->flush();
            $this->data['value'] = $menuItem->isPublic();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }
}
