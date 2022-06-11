<?php

namespace SovicCms\Repository;

use Doctrine\ORM\EntityRepository;
use SovicCms\Entity\Tag;

class PostRepository extends EntityRepository
{
    public function findPublic(int $limit = null, int $offset = null): array
    {
        return $this->findBy(
            ['public' => true],
            ['published' => 'DESC', 'id' => 'DESC'],
            $limit,
            $offset,
        );
    }

    public function countPublic(): int
    {
        return $this->count(['public' => true]);
    }

}
