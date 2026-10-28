<?php

namespace Sovic\Cms\Controller\Admin;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Trait\AiControllerTrait;
use Sovic\Cms\Controller\Admin\Trait\GalleryControllerTrait;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Entity\PageGroup;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Page\PageFactory;
use Sovic\Cms\Repository\PageGroupRepository;
use Sovic\Cms\Repository\PageRepository;
use Sovic\Cms\Tag\TagName;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class PageController extends AdminBaseController
{
    use AiControllerTrait;
    use GalleryControllerTrait;

    protected string $basePublicUrl;
    protected string $baseGalleryUrl;
    protected bool $enableTags;
    protected array $aiPreviewCss;
    protected string $aiPreviewBodyClass;
    protected string $aiPreviewWrapperClass;

    public function __construct(
        EntityManagerInterface                   $entityManager,
        #[Autowire('%base_public_url%')] string  $basePublicUrl,
        #[Autowire('%base_gallery_url%')] string $baseGalleryUrl,
        #[Autowire('%page_enable_tags%')] bool   $enableTags,
        #[Autowire('%ai_preview_css%')] array    $aiPreviewCss,
        #[Autowire('%ai_preview_body_class%')] string $aiPreviewBodyClass,
        #[Autowire('%ai_preview_wrapper_class%')] string $aiPreviewWrapperClass,
    ) {
        parent::__construct($entityManager);
        $this->basePublicUrl = $basePublicUrl;
        $this->baseGalleryUrl = $baseGalleryUrl;
        $this->enableTags = $enableTags;
        $this->aiPreviewCss = $aiPreviewCss;
        $this->aiPreviewBodyClass = $aiPreviewBodyClass;
        $this->aiPreviewWrapperClass = $aiPreviewWrapperClass;
    }

    #[Route(
        '/admin/page/list',
        name: 'admin:page:list',
    )]
    public function list(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        Request                   $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var PageRepository $repo */
        $repo = $em->getRepository(Page::class);

        /** @var PageGroupRepository $groupRepo */
        $groupRepo = $em->getRepository(PageGroup::class);
        $groups = $groupRepo->findAllOrderedByName();

        if ($request->query->has('group_id')) {
            $groupId = (int) $request->query->get('group_id');

            $sr = $factory->createFromRequest($request);
            $sr->setPaginationRoute('admin:page:list');

            $pages = $repo->findByGroupId($groupId, $sr);
            $total = $repo->countByGroupId($groupId, $sr);

            $currentGroup = $groupId > 0
                ? $em->getRepository(PageGroup::class)->find($groupId)
                : null;

            $this->assign('base_public_url', $this->basePublicUrl);
            $this->assign('current_group', $currentGroup);
            $this->assign('group_id', $groupId);
            $this->assign('pages', $pages);
            $this->assign('pagination', $sr->getPagination($total));
            $this->assign('query', array_merge($sr->toArray(), ['group_id' => $groupId]));
            $this->assign('view', 'pages');
        } else {
            $this->assign('group_counts', $repo->countPerGroup($groups));
            $this->assign('unsorted_count', $repo->countUnsorted());
            $this->assign('view', 'groups');
        }
        $this->assign('groups', $groups);

        return $this->render('@CmsBundle/admin/page/list.html.twig');
    }

    #[Route(
        '/admin/page/edit/{id}',
        name: 'admin:page:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function edit(
        ?int                   $id,
        EntityManagerInterface $em,
        PageFactory            $pageFactory,
        Request                $request,
        TranslatorInterface    $t,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repo = $em->getRepository(Page::class);
        $page = $repo->find($id);

        if ($page === null) {
            $page = new Page();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\Page::class, $page);
        if ($form->has('tags') && $em->contains($page)) {
            $pageTags = array_map(static fn(Tag $tag) => $tag->getName(), $pageFactory->loadByEntity($page)->getTags());
            $form->get('tags')->setData(implode(',', $pageTags));
        }
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($page->isPublic() && $page->getPublishedAt() === null) {
                    $page->setPublishedAt(new DateTimeImmutable());
                } elseif (!$page->isPublic()) {
                    $page->setPublishedAt(null);
                }

                // urlId optional in form, generated from name if empty
                $urlId = trim((string) $form->get('urlId')->getData());
                if ($urlId === '') {
                    $excludeId = $em->contains($page) ? $page->getId() : null;
                    /** @var PageRepository $repo */
                    $urlId = $repo->createUniqueUrlId($page->getName(), $page->getProject(), $excludeId);
                }
                $page->setUrlId($urlId);

                $em->persist($page);
                $em->flush();

                if ($form->has('tags')) {
                    $tagNames = TagName::parseList($form->get('tags')->getData());
                    $pageFactory->loadByEntity($page)->syncTagsByNames($tagNames);
                }

                try {
                    $this->addFlash('success', $t->trans('flash.saved', domain: 'page'));
                } catch (Throwable) {
                }

                return $this->redirectToRoute('admin:page:edit', ['id' => $page->getId()]);
            }

            try {
                $this->addFlash('error', $t->trans('flash.form_error', domain: 'page'));
            } catch (Throwable) {
            }
        }

        $editing = $id > 0;

        $pagePublicUrl = $this->basePublicUrl;
        if ($editing && $page->getId() !== null) {
            $model = $pageFactory->loadByEntity($page);
            $this->assignGalleries($model, ['page'], $this->baseGalleryUrl);
            $pagePublicUrl = $model->getPublicUrl($this->basePublicUrl);
        }

        $this->assign('ai_enabled', $this->isAiEnabled($em));
        $this->assign('ai_preview_body_class', $this->aiPreviewBodyClass);
        $this->assign('ai_preview_css', $this->aiPreviewCss);
        $this->assign('ai_preview_wrapper_class', $this->aiPreviewWrapperClass);
        $this->assign('base_public_url', $this->basePublicUrl);
        $this->assign('editing', $editing);
        $this->assign('form', $form->createView());
        $this->assign('page', $page);
        $this->assign('page_enable_tags', $this->enableTags);
        $this->assign('page_public_url', $pagePublicUrl);

        return $this->render('@CmsBundle/admin/page/detail.html.twig');
    }

    #[Route(
        '/admin/page/clone/{id}',
        name: 'admin:page:clone',
        requirements: ['id' => '\d+'],
    )]
    public function clone(
        int                    $id,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repo = $em->getRepository(Page::class);
        $source = $repo->find($id);

        if ($source === null) {
            return $this->redirectToRoute('admin:page:list');
        }

        $clone = clone $source;
        $clone->setUrlId($source->getUrlId() . '-copy-' . time());
        $clone->setName($source->getName() . ' (kopie)');
        $clone->setIsPublic(false);
        $clone->setIsInSitemap(true);
        $clone->setPublishedAt(null);
        $clone->setLastUpdateDate(null);

        $em->persist($clone);
        $em->flush();

        return $this->redirectToRoute('admin:page:edit', ['id' => $clone->getId()]);
    }
}
