<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Repository\PostRepository;
use Sovic\Common\Pagination\Pagination;
use Symfony\Component\HttpFoundation\Request;
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
        $pagination = new Pagination($repo->count(), $perPage);
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
        '/admin/post/edit/{id}',
        name: 'admin:post:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function postEdit(
        ?int                   $id,
        EntityManagerInterface $em,
        PostFactory            $postFactory,
        Request                $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repo = $em->getRepository(Post::class);
        $post = $repo->find($id);

        if ($post === null) {
            $post = new Post();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\Post::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($post->isPublic() && $post->getPublishDate() === null) {
                    $post->setPublishDate(new DateTimeImmutable());
                }

                /** @var \Sovic\Cms\Post\Post $model */
                $model = $postFactory->loadByEntity($post);
                $model->save();

                $this->addFlash('success', 'Příspěvek byl uložen.');

                return $this->redirectToRoute('admin:post:edit', ['id' => $post->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $this->assign('post', $post);
        $this->assign('form', $form->createView());

        $this->assign('auth_user', $this->getUser());

        return $this->render('@CmsBundle/admin/post/edit.html.twig');
    }
}
