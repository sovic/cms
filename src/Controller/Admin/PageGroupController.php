<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\PageGroup;
use Sovic\Cms\Form\Admin\PageGroupType;
use Sovic\Cms\Repository\PageGroupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageGroupController extends AdminBaseController
{
    #[Route(
        '/admin/page-group/edit/{id}',
        name: 'admin:page-group:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function edit(
        ?int                   $id,
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var PageGroupRepository $repo */
        $repo = $em->getRepository(PageGroup::class);
        $pageGroup = $repo->find($id);

        if ($pageGroup === null) {
            $pageGroup = new PageGroup();
        }

        $form = $this->createForm(PageGroupType::class, $pageGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($pageGroup);
            $em->flush();

            return $this->redirectToRoute('admin:page:list');
        }

        $editing = $id > 0;

        $this->assign('editing', $editing);
        $this->assign('form', $form->createView());
        $this->assign('page_group', $pageGroup);

        return $this->render('@CmsBundle/admin/page-group/edit.html.twig');
    }
}
