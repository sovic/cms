<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\Page;

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
