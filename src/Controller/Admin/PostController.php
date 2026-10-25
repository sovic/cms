<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Trait\GalleryControllerTrait;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Repository\PostRepository;
use Sovic\Cms\Tag\TagName;
use Sovic\Common\Pagination\Pagination;
use Sovic\Common\Project\Project;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class PostController extends AdminBaseController
{
    use GalleryControllerTrait;

    protected string $galleryBaseUrl;
    protected string $basePublicUrl;

    public function __construct(
        EntityManagerInterface                   $entityManager,
        #[Autowire('%base_gallery_url%')] string $galleryBaseUrl,
        #[Autowire('%base_public_url%')] string  $basePublicUrl,
    ) {
        parent::__construct($entityManager);
        $this->galleryBaseUrl = $galleryBaseUrl;
        $this->basePublicUrl = $basePublicUrl;
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
        Project                $project,
        Request                $request,
        TranslatorInterface    $t,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $post = $postFactory->loadById($id);
        if ($post === null) {
            $post = $postFactory->loadNew($project);
        }

        $entity = $post->getEntity();
        $form = $this->createForm(\Sovic\Cms\Form\Admin\Post::class, $entity);
        if ($form->has('tags') && $em->contains($entity)) {
            $postTags = array_map(static fn(Tag $tag) => $tag->getName(), $post->getTags());
            $form->get('tags')->setData(implode(',', $postTags));
        }
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$em->contains($entity)) {
                    $entity->setHeading($entity->getName());
                    $entity->setProject($project->getEntity());
                }

                $post->save();

                if ($form->has('tags')) {
                    $post->syncTagsByNames(TagName::parseList($form->get('tags')->getData()));
                }

                try {
                    $this->addFlash('success', $t->trans('flash.saved', domain: 'post'));
                } catch (Throwable) {
                }

                return $this->redirectToRoute('admin:post:edit', ['id' => $entity->getId()]);
            }

            try {
                $this->addFlash('error', $t->trans('flash.form_error', domain: 'post'));
            } catch (Throwable) {
            }
        }

        $editing = $id > 0;

        $postPublicUrl = null;
        if ($editing && $entity->getId() !== null) {
            $this->assignGalleries($post, ['post', 'documents'], $this->galleryBaseUrl);

            if ($post->getEntity()->isPublic()) {
                $postPublicUrl = $post->getUrl();
            }
        }

        $this->assign('auth_user', $this->getUser());
        $this->assign('editing', $editing);
        $this->assign('form', $form->createView());
        $this->assign('post', $entity);
        $this->assign('post_public_url', $postPublicUrl);

        return $this->render('@CmsBundle/admin/post/edit.html.twig');
    }
}
