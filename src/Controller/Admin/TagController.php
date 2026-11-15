<?php

namespace Sovic\Cms\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Entity\TagGroup;
use Sovic\Cms\Repository\TagGroupRepository;
use Sovic\Cms\Repository\TagRepository;
use Sovic\Cms\Tag\TagName;
use Sovic\Common\DataList\BasicSearchRequestFactory;
use Sovic\Common\Project\Project;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

class TagController extends AdminBaseController
{
    /**
     * Two views:
     * - groups overview (default): groups with tag counts and tag preview
     * - tags list: when group_id (0 = without group), view=tags or search is given
     */
    #[Route(
        '/admin/tag/list',
        name: 'admin:tag:list',
    )]
    public function list(
        EntityManagerInterface    $em,
        BasicSearchRequestFactory $factory,
        Project                   $project,
        Request                   $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $searchRequest = $factory->createFromRequest($request);
        $searchRequest->setPaginationRoute('admin:tag:list');

        $entity = $project->getEntity();

        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);
        /** @var TagGroupRepository $groupRepo */
        $groupRepo = $em->getRepository(TagGroup::class);

        $groups = $groupRepo->findOrdered($entity);

        $groupIdParam = (string) $request->query->get('group_id', '');
        $groupId = $groupIdParam === '' ? null : (int) $groupIdParam;

        $isTagsView = $groupId !== null
            || $request->query->get('view') === 'tags'
            || !empty($searchRequest->getSearch());

        if ($isTagsView) {
            $tags = $repo->findBySearchRequest($searchRequest, $entity, $groupId);
            $total = $repo->countBySearchRequest($searchRequest, $entity, $groupId);

            $currentGroup = $groupId > 0 ? $groupRepo->findOneById($groupId, $entity) : null;

            $query = $searchRequest->toArray();
            $query['view'] = 'tags';
            if ($groupId !== null) {
                $query['group_id'] = $groupId;
            }

            $this->assign('current_group', $currentGroup);
            $this->assign('group_id', $groupId);
            $this->assign('pagination', $searchRequest->getPagination($total));
            $this->assign('query', $query);
            $this->assign('tags', $tags);
            $this->assign('usage', $repo->countUsage(array_map(static fn(Tag $tag) => $tag->getId(), $tags)));
            $this->assign('view', 'tags');
        } else {
            $this->assign('group_counts', $repo->countPerGroup($entity));
            $this->assign('group_previews', $repo->findPreviewPerGroup($entity));
            $this->assign('query', $searchRequest->toArray());
            $this->assign('total_count', $repo->countAll($entity));
            $this->assign('ungrouped_count', $repo->countUngrouped($entity));
            $this->assign('view', 'groups');
        }
        $this->assign('groups', $groups);

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
        Project                $project,
        Request                $request,
        TranslatorInterface    $t,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);
        $tag = $repo->find($id);

        if ($tag === null) {
            $tag = new Tag();

            // preselect group when creating tag from group detail
            $groupId = (int) $request->query->get('group_id', 0);
            if ($groupId > 0) {
                /** @var TagGroupRepository $groupRepo */
                $groupRepo = $em->getRepository(TagGroup::class);
                $tag->setTagGroup($groupRepo->findOneById($groupId, $project->getEntity()));
            }
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\Tag::class, $tag, [
            'project' => $project->getEntity(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if (!$em->contains($tag)) {
                    $tag->setProject($project->getEntity());
                }

                $tag->setName(TagName::normalize((string) $tag->getName()));
                if (empty($tag->getUrlId())) {
                    $tag->setUrlId($repo->createUniqueUrlId($tag->getName()));
                }

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
        $this->assign('usage', $editing ? ($repo->countUsage([$tag->getId()])[$tag->getId()] ?? null) : null);

        return $this->render('@CmsBundle/admin/tag/edit.html.twig');
    }
}
