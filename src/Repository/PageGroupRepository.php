<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\PageGroup;

/**
 * @method PageGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PageGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PageGroup[]    findAll()
 * @method PageGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageGroupRepository extends EntityRepository
{
    /**
     * @return PageGroup[]
     */
    public function findAllOrderedByName(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }
}
