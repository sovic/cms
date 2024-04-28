<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Project\Project;

class PageRepository extends EntityRepository
{
    /**
     * @return Page[]
     */
    public function findPublic(Project $project, int $limit = null, int $offset = null): array
    {
        return $this->findBy(
            [
                'project' => $project->getEntity(),
                'public' => true,
            ],
            ['id' => 'DESC'],
            $limit,
            $offset,
        );
    }
}
