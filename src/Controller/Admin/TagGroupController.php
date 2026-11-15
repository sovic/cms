<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Entity\TagGroup;
use Sovic\Cms\Form\Admin\TagGroupType;
use Sovic\Cms\Repository\TagGroupRepository;
use Sovic\Cms\Repository\TagRepository;
use Sovic\Common\Project\Project;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class TagGroupController extends AdminBaseController
{
    #[Route(
        '/admin/tag-group/edit/{id}',
        name: 'admin:tag-group:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function edit(
        ?int                   $id,
        EntityManagerInterface $em,
        Project                $project,
        Request                $request,
        TranslatorInterface    $t,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entity = $project->getEntity();

        /** @var TagGroupRepository $repo */
        $repo = $em->getRepository(TagGroup::class);
        /** @var TagRepository $tagRepo */
        $tagRepo = $em->getRepository(Tag::class);

        $tagGroup = $id > 0 ? $repo->findOneById($id, $entity) : null;
        if ($id > 0 && $tagGroup === null) {
            throw $this->createNotFoundException();
        }

        if ($tagGroup === null) {
            $tagGroup = new TagGroup();
            $tagGroup->setPosition($repo->getNextPosition($entity));
        }

        $form = $this->createForm(TagGroupType::class, $tagGroup);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$em->contains($tagGroup)) {
                    $tagGroup->setProject($entity);
                }

                $tagGroup->setName(trim($tagGroup->getName()));
                $urlIdSource = trim((string) $tagGroup->getUrlId()) ?: $tagGroup->getName();
                $tagGroup->setUrlId($repo->createUniqueUrlId(
                    $urlIdSource,
                    $entity,
                    $em->contains($tagGroup) ? $tagGroup->getId() : null,
                ));

                $em->persist($tagGroup);
                $em->flush();

                try {
                    $this->addFlash('success', $t->trans('flash.group_saved', domain: 'tag'));
                } catch (Throwable) {
                }

                return $this->redirectToRoute('admin:tag-group:edit', ['id' => $tagGroup->getId()]);
            }

            try {
                $this->addFlash('error', $t->trans('flash.form_error', domain: 'tag'));
            } catch (Throwable) {
            }
        }

        $editing = $id > 0;
        $tags = $editing ? $tagRepo->findByGroup($tagGroup) : [];

        $this->assign('editing', $editing);
        $this->assign('form', $form->createView());
        $this->assign('tag_group', $tagGroup);
        $this->assign('tags', $tags);

        return $this->render('@CmsBundle/admin/tag-group/edit.html.twig');
    }
}
