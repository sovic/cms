<?php

namespace SovicCms\Repository;

use Doctrine\ORM\EntityRepository;
use SovicCms\Entity\Page;

class PageRepository extends EntityRepository
{
    /**
     * @param int|null $limit
     * @param int|null $offset
     * @return Page[]
     */
    public function findPublic(int $limit = null, int $offset = null): array
    {
        return $this->findBy(
            ['public' => true],
            ['id' => 'DESC'],
            $limit,
            $offset,
        );
    }
}
