<?php

namespace Sovic\Cms\Tag;

use Sovic\Cms\Entity\Tag as TagEntity;
use Sovic\Common\Project\ProjectEntityModelFactoryInterface;
use Sovic\Common\Project\ProjectEntityModelFactoryTrait;
use Sovic\Common\Model\EntityModelFactory;

final class TagFactory extends EntityModelFactory implements ProjectEntityModelFactoryInterface
{
    use ProjectEntityModelFactoryTrait;

    public function loadByEntity(?TagEntity $entity = null): ?Tag
    {
        return $this->loadEntityModel($entity, Tag::class);
    }

    public function loadById(int $id): ?Tag
    {
        $criteria = $this->getProjectSelectCriteria();
        $criteria['id'] = $id;

        return $this->loadModelBy(TagEntity::class, Tag::class, $criteria);
    }

    /**
     * @param int[] $ids
     * @return Tag[]|null
     */
    public function loadByIds(array $ids): ?array
    {
        $criteria = $this->getProjectSelectCriteria();
        $criteria['id'] = $ids;

        $repo = $this->getEntityManager()->getRepository(TagEntity::class);
        $entities = $repo->findBy($criteria);
        $result = [];
        foreach ($entities as $entity) {
            $result[] = $this->loadByEntity($entity);
        }

        return $result;
    }
}
