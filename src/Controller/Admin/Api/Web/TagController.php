<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Repository\TagRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TagController extends AbstractBaseApiController
{
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
}
