<?php

namespace Sovic\Cms\Project;

use Sovic\Cms\Entity\Setting;
use Sovic\Cms\ORM\AbstractEntityModel;

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

    public function getSettings(): ProjectSettings
    {
        if (isset(self::$settings)) {
            return self::$settings;
        }

        $items = $this->entityManager
            ->getRepository(Setting::class)
            ->findBy(['project' => $this->entity]);
        $parameters = [];
        $templateKeys = [];
        foreach ($items as $item) {
            $parameters[$item->getGroup() . '.' . $item->getKey()] = $item->getValue();
            if ($item->isTemplateEnabled()) {
                $templateKeys[$item->getGroup() . '.' . $item->getKey()] = $item->getValue();
            }
        }

        self::$settings = new ProjectSettings($parameters, $templateKeys);

        return self::$settings;
    }
}
