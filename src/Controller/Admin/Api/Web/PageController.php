<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\Page;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractBaseApiController
{
    private const ToggleableFields = [
        'is_public',
        'is_in_sitemap',
        'has_toc',
    ];

    #[Route(
        '/admin/api/web/page/{id}/toggle-state',
        name: 'admin:api:web:page:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $page = $em->getRepository(Page::class)->find($id);
        if ($page === null) {
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
            $page->setIsPublic($state);
            if ($page->isPublic() && $page->getPublishedAt() === null) {
                $page->setPublishedAt(new DateTimeImmutable());
            } elseif (!$page->isPublic()) {
                $page->setPublishedAt(null);
            }

            $em->flush();

            $this->data['value'] = $page->isPublic();

            return $this->sendSuccess();
        }

        if ($field === 'is_in_sitemap') {
            $page->setIsInSitemap($state);
            $em->flush();

            $this->data['value'] = $page->isInSitemap();

            return $this->sendSuccess();
        }

        if ($field === 'has_toc') {
            $page->setHasToc($state);
            $em->flush();

            $this->data['value'] = $page->hasToc();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }
}
