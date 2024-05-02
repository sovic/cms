<?php

namespace Sovic\Cms\Controller;

use Sovic\Cms\Project\Project;

interface ProjectControllerInterface
{
    public function setProject(Project $project): void;
}
