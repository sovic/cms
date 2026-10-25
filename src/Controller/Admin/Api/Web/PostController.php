<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Post\PostFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractBaseApiController
{
    private const ToggleableFields = [
        'is_private',
        'is_public',
    ];

    #[Route(
        '/admin/api/web/post/{id}/toggle-state',
        name: 'admin:api:web:post:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int         $id,
        PostFactory $postFactory,
        Request     $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $post = $postFactory->loadById($id);
        if ($post === null) {
            return $this->sendFail(404);
        }

        $data = $this->getRequestData($request);
        $field = $data['field'] ?? null;
        if (!in_array($field, self::ToggleableFields, true)) {
            $this->addError('invalid_field');

            return $this->sendFail();
        }

        $state = (bool) ($data['state'] ?? false);

        if ($field === 'is_public') {
            $post->setIsPublic($state);
            $post->flush();

            $this->data['value'] = $post->getEntity()->isPublic();

            return $this->sendSuccess();
        }

        if ($field === 'is_private') {
            $post->setIsPrivate($state);
            $post->flush();

            $this->data['value'] = $post->getEntity()->isPrivate();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }
}
