<?php

namespace Sovic\Cms\Project;

interface ProjectEntityModelFactoryInterface
{
    public function setProject(?Project $project): void;

    public function getProjectSelectCriteria(): array;
}
