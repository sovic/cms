<?php

namespace Sovic\Cms\Project;

trait ProjectEntityModelFactoryTrait
{
    private ?Project $project = null;

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getProjectSelectCriteria(): array
    {
        $criteria = [];
        if (null !== $this->project) {
            $criteria['project'] = $this->project->entity;
        }

        return $criteria;
    }
}
