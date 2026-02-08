<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Repository\PageRepository;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Sovic\Common\DataList\Enum\VisibilityId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

trait PageControllerTrait
{
    #[Route(
        '/admin/page/list',
        name: 'admin:page:list',
    )]
    public function pageList(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        Request                   $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $sr = $factory->createFromRequest($request);
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
    public function pageEdit(
        ?int                   $id,
        EntityManagerInterface $em,
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

//                $this->addFlash('success', 'Stránka byla uložena.');

                return $this->redirectToRoute('admin:page:edit', ['id' => $page->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $this->assign('editing', $id > 0);
        $this->assign('page', $page);
        $this->assign('form', $form->createView());

        return $this->render('@CmsBundle/admin/page/edit.html.twig');
    }

    #[Route(
        '/admin/page/clone/{id}',
        name: 'admin:page:clone',
        requirements: ['id' => '\d+'],
    )]
    public function pageClone(
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
        $clone->setPublic(false);
        $clone->setPublishedAt(null);
        $clone->setLastUpdateDate(null);

        $em->persist($clone);
        $em->flush();

        return $this->redirectToRoute('admin:page:edit', ['id' => $clone->getId()]);
    }
}
