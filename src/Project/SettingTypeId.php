<?php

namespace Sovic\Cms\Project;

enum SettingTypeId: string
{
    case String = 'string';
    case Integer = 'int';
    case Boolean = 'bool';
    case Array = 'array';
}
