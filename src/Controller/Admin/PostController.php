<?php

namespace Sovic\Cms\Controller\Admin;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Trait\GalleryControllerTrait;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Repository\PostRepository;
use Sovic\Common\Pagination\Pagination;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AdminBaseController
{
    use GalleryControllerTrait;

    protected string $galleryBaseUrl;

    public function __construct(
        EntityManagerInterface                   $entityManager,
        #[Autowire('%gallery_base_url%')] string $galleryBaseUrl,
    ) {
        parent::__construct($entityManager);
        $this->galleryBaseUrl = $galleryBaseUrl;
    }

    #[Route(
        '/admin/post/list',
        name: 'admin:post:list',
    )]
    public function list(
        EntityManagerInterface $em,
        PostResultSetFactory   $postResultSetFactory,
        Request                $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $perPage = 50;

        /** @var PostRepository $repo */
        $repo = $em->getRepository(Post::class);

        $queryPage = $request->query->get('page');
        $pageNr = $queryPage ?? 1;
        $pagination = new Pagination($repo->count(), $perPage);
        if ($pageNr > $pagination->getPageCount()) {
            return $this->renderProject404();
        }
        $pagination->setCurrentPage($pageNr);

        $posts = $repo->findBy([], ['id' => 'DESC'], $perPage, ($pageNr - 1) * $perPage);

        $set = $postResultSetFactory->createFromEntities($posts);
        $set->setAddAuthors(true);
        $set->setAddCovers(true);
        $set->setGalleryBaseUrl($this->galleryBaseUrl);

        $this->assign('posts', $set->toArray());

        return $this->render('@CmsBundle/admin/post/list.html.twig');
    }

    #[Route(
        '/admin/post/edit/{id}',
        name: 'admin:post:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function edit(
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
                if (!$em->contains($post)) {
                    $post->setHeading($post->getName());
                    $post->setProject($this->project->getEntity());
                }

                /** @var \Sovic\Cms\Post\Post $model */
                $model = $postFactory->loadByEntity($post);
                $model->save();

                $this->addFlash('success', 'Příspěvek byl uložen.');

                return $this->redirectToRoute('admin:post:edit', ['id' => $post->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $editing = $id > 0;

        if ($editing && $post->getId() !== null) {
            $postModel = $postFactory->loadByEntity($post);
            $this->assignGalleries($postModel, ['post', 'documents'], $this->galleryBaseUrl);
        }

        $this->assign('auth_user', $this->getUser());
        $this->assign('editing', $editing);
        $this->assign('form', $form->createView());
        $this->assign('post', $post);

        return $this->render('@CmsBundle/admin/post/edit.html.twig');
    }
}
