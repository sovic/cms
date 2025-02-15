<?php

namespace Sovic\Cms\Page;

use Cocur\Slugify\Slugify;
use Sovic\Cms\Entity\Page as PageEntity;
use Sovic\Common\Project\ProjectEntityModelFactoryInterface;
use Sovic\Common\Project\ProjectEntityModelFactoryTrait;
use Sovic\Common\Model\EntityModelFactory;

final class PageFactory extends EntityModelFactory implements ProjectEntityModelFactoryInterface
{
    use ProjectEntityModelFactoryTrait;

    public function loadByEntity(?PageEntity $entity = null): ?Page
    {
        return $this->loadEntityModel($entity, Page::class);
    }

    public function loadById(int $id): ?Page
    {
        $criteria = $this->getProjectSelectCriteria();
        $criteria['id'] = $id;

        return $this->loadModelBy(PageEntity::class, Page::class, $criteria);
    }

    public function loadByUrlId(string $urlId, bool $allowPrivate = false): ?Page
    {
        $criteria = $this->getProjectSelectCriteria();
        $urlId = trim($urlId, '/\\'); // trim leading / trailing slashes
        $criteria['urlId'] = $urlId;

        $model = $this->loadModelBy(PageEntity::class, Page::class, $criteria);
        if (null === $model) {
            return null;
        }
        if (!$allowPrivate && !$model->entity->isPublic()) {
            return null;
        }

        return $model;
    }

    public function createDefault(
        string  $name,
        string  $heading,
        ?string $urlId = null,
        ?string $content = null,
    ): Page {
        $page = new PageEntity();
        if ($this->project) {
            $page->setProject($this->project->entity);
        }
        $page->setName($name);
        if (null === $urlId) {
            $slugify = new Slugify();
            $slugify->activateRuleSet('default');

            $urlId = $slugify->slugify($name);
        }
        $page->setUrlId($urlId);

        $page->setHeading($heading);
        $page->setMetaTitle($heading);
        $page->setMetaDescription($heading);

        $page->setContent($content);
        $page->setPublic(true);

        return $this->loadByEntity($page);
    }
}
