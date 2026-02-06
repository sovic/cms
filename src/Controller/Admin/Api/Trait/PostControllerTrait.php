<?php

namespace Sovic\Cms\Controller\Admin\Api\Trait;

use Sovic\Cms\Controller\Trait\ControllerAccessTrait;
use Sovic\Common\Controller\Trait\JsonResponseTrait;

trait PostControllerTrait
{
    use ControllerAccessTrait;
    use JsonResponseTrait;

//    protected function isAttributeGranted(string $attribute): bool
//    {
//        return $this->isGranted('ROLE_ADMIN');
//    }
//
//    #[Route(
//        '/admin/api/post/{id}/publish',
//        name: 'admin:api:post:publish',
//    )]
//    public function publish(
//        int                    $id,
//        EntityManagerInterface $em,
//        PostFactory            $postFactory,
//    ): Response {
//        $this->getRouteAccessDecision('admin:api:post:publish');
//
//        $post = $postFactory->loadById($id);
//        if (null === $post) {
//
//        }
//        $post->save(true);
//
//    }
}
