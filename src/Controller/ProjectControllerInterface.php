<?php

namespace Sovic\Cms\Controller;

use Sovic\Common\Project\Project;

interface ProjectControllerInterface
{
    public function setProject(Project $project): void;
}
