<?php

namespace Sovic\Cms\Project;

use Sovic\Cms\Entity\Setting;
use Sovic\Cms\ORM\AbstractEntityModel;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @property \Sovic\Cms\Entity\Project entity
 */
class Project extends AbstractEntityModel
{
    public function getId(): int
    {
        return $this->entity->getId();
    }

    public function getSettings(): ParameterBag
    {
        $items = $this->entityManager
            ->getRepository(Setting::class)
            ->findBy(['project' => $this->entity]);
        $parameters = [];
        foreach ($items as $item) {
            $parameters[$item->getGroup() . '.' . $item->getKey()] = $item->getValue();
        }

        return new ProjectSettings($parameters);
    }
}
