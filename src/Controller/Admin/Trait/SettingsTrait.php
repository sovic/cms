<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Sovic\Common\Entity\Setting;
use Sovic\Common\Enum\SettingTypeId;
use Sovic\Common\Project\Project;
use Sovic\Common\Project\SettingGroupId;
use Sovic\Common\Project\Settings;
use Symfony\Contracts\Service\Attribute\Required;

trait SettingsTrait
{
    private Settings $appSettings;

    #[Required]
    public function setAppSettings(Settings $appSettings): void
    {
        $this->appSettings = $appSettings;
    }

    protected function getSettingValue(
        SettingGroupId $group,
        string         $key,
        mixed          $default = null,
        ?Project       $project = null,
    ): mixed {
        return $this->appSettings->get($group->value, $key, $default);
    }

    protected function persistSettingValue(
        SettingGroupId $group,
        string         $key,
        mixed          $value,
        ?Project       $project = null,
        ?string        $description = null,
    ): void {
        $criteria = [
            'groupId' => $group,
            'key' => $key,
        ];
        if ($project) {
            $criteria['project'] = $project->entity;
        }

        $setting = $this->entityManager
            ->getRepository(Setting::class)
            ->findOneBy($criteria);

        if (!$setting) {
            $setting = new Setting();
            $setting->setGroupId($group);
            $setting->setKey($key);
            if ($project) {
                $setting->setProject($project->entity);
            }
        }
        $setting->setValue($value);
        $setting->setDescription($description ?? '');
        $type = SettingTypeId::String;
        if (is_bool($value)) {
            $type = SettingTypeId::Boolean;
        }
        if (is_int($value)) {
            $type = SettingTypeId::Integer;
        }
        if (is_array($value)) {
            $type = SettingTypeId::Array;
        }
        $setting->setType($type);

        $this->entityManager->persist($setting);
        $this->entityManager->flush();

        $this->appSettings->invalidate();
    }
}
