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
    private static ProjectSettings $settings;

    public function getId(): int
    {
        return $this->entity->getId();
    }

    public function getSettings(): ParameterBag
    {
        if (isset(self::$settings)) {
            return self::$settings;
        }

        $items = $this->entityManager
            ->getRepository(Setting::class)
            ->findBy(['project' => $this->entity]);
        $parameters = [];
        foreach ($items as $item) {
            $parameters[$item->getGroup() . '.' . $item->getKey()] = $item->getValue();
        }

        self::$settings = new ProjectSettings($parameters);

        return self::$settings;
    }
}
