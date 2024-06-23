<?php

namespace Sovic\Cms\Tag;

use Sovic\Cms\Entity\Tag as TagEntity;
use Sovic\Cms\Project\ProjectEntityModelFactoryInterface;
use Sovic\Cms\Project\ProjectEntityModelFactoryTrait;
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
}
