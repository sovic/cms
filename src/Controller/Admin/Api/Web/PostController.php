<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\Post;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractBaseApiController
{
    private const ToggleableFields = [
        'is_public',
    ];

    #[Route(
        '/admin/api/web/post/{id}/toggle-state',
        name: 'admin:api:web:post:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $post = $em->getRepository(Post::class)->find($id);
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
            // only public flag, publish date is not changed (published = public + publish date reached)
            $post->setPublic($state);

            $em->flush();

            $this->data['value'] = $post->isPublic();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }
}
