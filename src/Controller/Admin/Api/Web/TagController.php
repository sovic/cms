<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Repository\TagRepository;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TagController extends AbstractBaseApiController
{
    use JsonResponseTrait;

    private const ToggleableFields = [
        'is_public',
    ];

    #[Route(
        '/admin/api/web/tag/suggestions',
        name: 'admin:api:web:tag:suggestions',
        methods: ['GET'],
    )]
    public function suggestions(
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $q = trim((string) $request->query->get('q', ''));

        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);

        return $this->json($repo->findBySearch($q));
    }

    #[Route(
        '/admin/api/web/tag/{id}/delete',
        name: 'admin:api:web:tag:delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function delete(
        int                    $id,
        EntityManagerInterface $em,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = $em->getRepository(Tag::class)->find($id);
        if ($tag === null) {
            return $this->sendFail(404);
        }

        $em->remove($tag);
        $em->flush();

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/tag/{id}/toggle-state',
        name: 'admin:api:web:tag:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = $em->getRepository(Tag::class)->find($id);
        if ($tag === null) {
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
            $tag->setIsPublic((bool) $state);
            $em->flush();

            $this->data['value'] = $tag->isIsPublic();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }
}
