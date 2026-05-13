<?php

namespace Sovic\Cms\Enum;

interface SettingKeyInterface
{
    public function getFormField(): string;

    public function getSettingKey(): string;

    public function getDescription(): string;
}
