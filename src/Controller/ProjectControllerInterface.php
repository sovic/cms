<?php

namespace Sovic\Cms\Controller;

use Sovic\Common\Project\Project;
use Sovic\Common\Project\Settings;

interface ProjectControllerInterface
{
    public function setProject(Project $project): void;

    public function setSettings(Settings $settings): void;
}
