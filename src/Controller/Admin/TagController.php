<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Repository\TagRepository;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class TagController extends AdminBaseController
{
    #[Route(
        '/admin/tag/list',
        name: 'admin:tag:list',
    )]
    public function list(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        Request                   $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $searchRequest = $factory->createFromRequest($request);
        $searchRequest->setPaginationRoute('admin:tag:list');

        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);

        $project = null; // $this->project->getEntity();
        $tags = $repo->findBySearchRequest($searchRequest, $project);
        $total = $repo->countBySearchRequest($searchRequest, $project);

        $this->assign('pagination', $searchRequest->getPagination($total));
        $this->assign('query', $searchRequest->toArray());
        $this->assign('tags', $tags);

        return $this->render('@CmsBundle/admin/tag/list.html.twig');
    }

    #[Route(
        '/admin/tag/edit/{id}',
        name: 'admin:tag:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function edit(
        ?int                   $id,
        EntityManagerInterface $em,
        Request                $request,
        TranslatorInterface    $t,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repo = $em->getRepository(Tag::class);
        $tag = $repo->find($id);

        if ($tag === null) {
            $tag = new Tag();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\Tag::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
//                if (!$em->contains($tag)) {
//                    $tag->setProject($this->project->getEntity());
//                }

                $em->persist($tag);
                $em->flush();

                try {
                    $this->addFlash('success', $t->trans('flash.saved', domain: 'tag'));
                } catch (Throwable) {
                }

                return $this->redirectToRoute('admin:tag:edit', ['id' => $tag->getId()]);
            }

            try {
                $this->addFlash('error', $t->trans('flash.form_error', domain: 'tag'));
            } catch (Throwable) {
            }
        }

        $editing = $id > 0;

        $this->assign('editing', $editing);
        $this->assign('form', $form->createView());
        $this->assign('tag', $tag);

        return $this->render('@CmsBundle/admin/tag/edit.html.twig');
    }
}
