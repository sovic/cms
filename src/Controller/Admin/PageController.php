<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Trait\GalleryControllerTrait;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Page\PageFactory;
use Sovic\Cms\Repository\PageRepository;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Sovic\Common\DataList\Enum\VisibilityId;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AdminBaseController
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
        '/admin/page/list',
        name: 'admin:page:list',
    )]
    public function list(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        Request                   $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $sr = $factory->createFromRequest($request);
        $sr->setPaginationRoute('admin:page:list');
        $sr->setVisibilityId(VisibilityId::All);

        /** @var PageRepository $repo */
        $repo = $em->getRepository(Page::class);
        $pages = $repo->findBySearchRequest($sr);
        $total = $repo->countBySearchRequest($sr);

        $this->assign('pages', $pages);
        $this->assign('pagination', $sr->getPagination($total));
        $this->assign('query', $sr->toArray());

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
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repo = $em->getRepository(Page::class);
        $page = $repo->find($id);

        if ($page === null) {
            $page = new Page();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\Page::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($page);
                $em->flush();

                $this->addFlash('success', 'Stránka byla uložena.');

                return $this->redirectToRoute('admin:page:edit', ['id' => $page->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $editing = $id > 0;

        if ($editing && $page->getId() !== null) {
            $model = $pageFactory->loadByEntity($page);
            $this->assignGalleries($model, ['page'], $this->galleryBaseUrl);
        }

        $this->assign('editing', $editing);
        $this->assign('form', $form->createView());
        $this->assign('page', $page);

        return $this->render('@CmsBundle/admin/page/edit.html.twig');
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
        $clone->setPublishedAt(null);
        $clone->setLastUpdateDate(null);

        $em->persist($clone);
        $em->flush();

        return $this->redirectToRoute('admin:page:edit', ['id' => $clone->getId()]);
    }
}
