<?php

namespace SovicCms\Repository;

use Doctrine\ORM\EntityRepository;

class PageRepository extends EntityRepository
{
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
