<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Trait\GalleryControllerTrait;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostResultSetFactory;
use Sovic\Cms\Post\PostSearchRequest;
use Sovic\Cms\Post\PostSort;
use Sovic\Cms\Repository\PostRepository;
use Sovic\Cms\Tag\TagName;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Sovic\Common\DataList\Enum\VisibilityId;
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
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        PostResultSetFactory      $postResultSetFactory,
        Project                   $project,
        Request                   $request,
    ): Response {
        return $this->renderList($em, $factory, $postResultSetFactory, $project, $request, false);
    }

    #[Route(
        '/admin/post/drafts',
        name: 'admin:post:drafts',
    )]
    public function drafts(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        PostResultSetFactory      $postResultSetFactory,
        Project                   $project,
        Request                   $request,
    ): Response {
        return $this->renderList($em, $factory, $postResultSetFactory, $project, $request, true);
    }

    /**
     * @param bool $isDraft only drafts (posts without publish date)
     */
    private function renderList(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        PostResultSetFactory      $postResultSetFactory,
        Project                   $project,
        Request                   $request,
        bool                      $isDraft,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $searchRequest = new PostSearchRequest();
        // admin list shows all posts (incl. not published), unless visibility is requested
        $searchRequest->setVisibilityId(VisibilityId::All);
        $factory->loadDefaultSearchRequest($request, $searchRequest);
        $searchRequest->setPaginationRoute($isDraft ? 'admin:post:drafts' : 'admin:post:list');
        $searchRequest->setProject($project);
        $searchRequest->setIsDraft($isDraft);
        if ($isDraft) {
            // no publish date to sort by
            $searchRequest->setPostSort(PostSort::Latest);
        }

        /** @var PostRepository $repo */
        $repo = $em->getRepository(Post::class);

        $posts = $repo->findByRequest($searchRequest);
        $total = $repo->countByRequest($searchRequest);

        $set = $postResultSetFactory->createFromEntities($posts);
        $set->setAddAuthors(true);
        $set->setAddCovers(true);
        $set->setGalleryBaseUrl($this->galleryBaseUrl);

        $this->assign('pagination', $searchRequest->getPagination($total));
        $this->assign('posts', $set->toArray());
        $this->assign('query', $searchRequest->toArray());

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
