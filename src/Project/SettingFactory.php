<?php

namespace Sovic\Cms\Project;

use Sovic\Cms\Entity\Setting;
use Sovic\Cms\ORM\EntityModelFactory;

final class SettingFactory extends EntityModelFactory
{
    public function create(
        Project       $project,
        string        $group,
        string        $key,
        string        $value,
        ?string       $description = null,
        SettingTypeId $type = SettingTypeId::String
    ): Setting {
        $setting = new Setting();
        $setting->setProject($project->entity);
        $setting->setGroup($group);
        $setting->setKey($key);
        $setting->setValue($value);
        $setting->setDescription($description);
        $setting->setType($type);

        return $setting;
    }
}
