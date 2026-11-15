<?php

namespace Sovic\Cms\Controller\Admin\Api\Web;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Admin\Api\AbstractBaseApiController;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Entity\TagGroup;
use Sovic\Cms\Repository\TagGroupRepository;
use Sovic\Cms\Repository\TagRepository;
use Sovic\Cms\Tag\TagName;
use Sovic\Common\Controller\Trait\JsonResponseTrait;
use Sovic\Common\Project\Project;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TagController extends AbstractBaseApiController
{
    use JsonResponseTrait;

    private const ToggleableFields = [
        'is_public',
    ];

    #[Route(
        '/admin/api/web/tag/suggestions',
        name: 'admin:api:web:tag:suggestions',
        methods: ['GET'],
    )]
    public function suggestions(
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $q = trim((string) $request->query->get('q', ''));

        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);

        return $this->json($repo->findBySearch($q));
    }

    #[Route(
        '/admin/api/web/tag/create',
        name: 'admin:api:web:tag:create',
        methods: ['POST'],
    )]
    public function create(
        EntityManagerInterface $em,
        Project                $project,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $this->getRequestData($request);
        $name = TagName::normalize((string) ($data['name'] ?? ''));
        if ($name === '') {
            $this->addError('invalid_name');

            return $this->sendFail();
        }

        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);

        $tag = $repo->findOneBy(['name' => $name]);
        if ($tag === null) {
            $tag = new Tag();
            $tag->setName($name);
            $tag->setUrlId($repo->createUniqueUrlId($name));
            $tag->setIsPublic(true);
            $tag->setProject($project->getEntity());

            $em->persist($tag);
            $em->flush();
        }

        $this->data['id'] = $tag->getId();
        $this->data['name'] = $tag->getName();
        $this->data['url_id'] = $tag->getUrlId();

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/tag/{id}/delete',
        name: 'admin:api:web:tag:delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function delete(
        int                    $id,
        EntityManagerInterface $em,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = $em->getRepository(Tag::class)->find($id);
        if ($tag === null) {
            return $this->sendFail(404);
        }

        $em->remove($tag);
        $em->flush();

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/tag/{id}/toggle-state',
        name: 'admin:api:web:tag:toggle-state',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function toggleState(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = $em->getRepository(Tag::class)->find($id);
        if ($tag === null) {
            return $this->sendFail(404);
        }

        $data = $this->getRequestData($request);
        $field = $data['field'] ?? null;
        if (!in_array($field, self::ToggleableFields, true)) {
            $this->addError('invalid_field');

            return $this->sendFail();
        }

        $state = $data['state'] ?? null;

        if ($field === 'is_public') {
            $tag->setIsPublic((bool) $state);
            $em->flush();

            $this->data['value'] = $tag->isIsPublic();

            return $this->sendSuccess();
        }

        return $this->sendFail();
    }

    /**
     * Bulk assign tags to group (data list group action format).
     * Body: {"ids": [1, 2], "parameters": {"group_id": 3}}, group_id null/0 removes tags from group.
     */
    #[Route(
        '/admin/api/web/tag/set-group',
        name: 'admin:api:web:tag:set-group',
        methods: ['POST'],
    )]
    public function setGroup(
        EntityManagerInterface $em,
        Project                $project,
        Request                $request,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = $this->getRequestData($request);
        $ids = array_values(array_filter(array_map('intval', (array) ($data['ids'] ?? []))));
        if (empty($ids)) {
            $this->addError('no_tags_selected');

            return $this->sendFail();
        }

        $entity = $project->getEntity();
        $groupId = (int) ($data['parameters']['group_id'] ?? 0);
        $group = null;
        if ($groupId > 0) {
            /** @var TagGroupRepository $groupRepo */
            $groupRepo = $em->getRepository(TagGroup::class);
            $group = $groupRepo->findOneById($groupId, $entity);
            if ($group === null) {
                $this->addError('invalid_group');

                return $this->sendFail(404);
            }
        }

        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);
        $count = $repo->setGroupForIds($ids, $group, $entity);

        $this->data['count'] = $count;
        $this->data['message'] = $group
            ? sprintf('Štítky přesunuty do skupiny „%s“ (%d).', $group->getName(), $count)
            : sprintf('Štítky odebrány ze skupiny (%d).', $count);

        return $this->sendSuccess();
    }

    #[Route(
        '/admin/api/web/tag-group/{id}/delete',
        name: 'admin:api:web:tag-group:delete',
        requirements: ['id' => '\d+'],
        methods: ['POST'],
    )]
    public function deleteGroup(
        int                    $id,
        EntityManagerInterface $em,
        Project                $project,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var TagGroupRepository $groupRepo */
        $groupRepo = $em->getRepository(TagGroup::class);
        $group = $groupRepo->findOneById($id, $project->getEntity());
        if ($group === null) {
            return $this->sendFail(404);
        }

        // tags stay, only lose group (FK on delete SET NULL), done explicitly for DBs without FK
        /** @var TagRepository $repo */
        $repo = $em->getRepository(Tag::class);
        $repo->setGroupForIds(
            array_map(static fn(Tag $tag) => $tag->getId(), $repo->findByGroup($group)),
            null,
        );

        $em->remove($group);
        $em->flush();

        return $this->sendSuccess();
    }
}
