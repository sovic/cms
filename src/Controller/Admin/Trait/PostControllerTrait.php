<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Repository\PostRepository;
use Sovic\Common\Pagination\Pagination;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

trait PostControllerTrait
{
    #[Route(
        '/admin/post/list',
        name: 'admin:post:list',
    )]
    public function postList(
        EntityManagerInterface $em,
        PostResultSetFactory   $postResultSetFactory,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->assign('auth_user', $this->getUser());

        $perPage = 50;

        /** @var PostRepository $repo */
        $repo = $em->getRepository(Post::class);

        $pageNr = 1;
        $pagination = new Pagination($repo->count([]), $perPage);
        if ($pageNr > $pagination->getPageCount()) {
            return $this->renderProject404();
        }
        $pagination->setCurrentPage($pageNr);

        $posts = $repo->findBy([], ['id' => 'DESC'], $perPage, ($pageNr - 1) * $perPage);

        $postsResultSet = $postResultSetFactory->createFromEntities($posts);
        $postsResultSet->setAddAuthors(true);
        $postsResultSet->setAddCovers(true);
        $postsResultSet->setGalleryBaseUrl('https://www.sovic.cz/gallery');

        $this->assign('posts', $postsResultSet->toArray());

        return $this->render('@CmsBundle/admin/post/list.html.twig');
    }

    #[Route(
        '/admin/post/edit',
        name: 'admin:post:edit',
    )]
    public function postEdit(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->assign('auth_user', $this->getUser());

        return $this->render('@CmsBundle/admin/post/edit.html.twig');
    }
}
