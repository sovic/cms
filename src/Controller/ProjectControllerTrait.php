<?php

namespace Sovic\Cms\Controller;

use Sovic\Cms\Project\Project;
use Sovic\Cms\Project\ProjectFactory;
use Symfony\Component\HttpFoundation\Request;

trait ProjectControllerTrait
{
    private Project $project;

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function initializeProjectController(
        ProjectFactory $projectFactory,
        Request        $request,
    ): void {
        $this->setProject($projectFactory->loadByRequest($request));
        $this->assignProjectData();
    }

    public function assignProjectData(): void
    {
        $entity = $this->project->entity;

        $this->assign('project', $entity->getSlug());
    }
}
