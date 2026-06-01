<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\Tag;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends EntityRepository
{
    /**
     * @return string[]
     */
    public function findBySearch(string $search = '', int $limit = 30): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('t.name');
        $qb->from(Tag::class, 't');
        $qb->where('t.name IS NOT NULL');
        if ($search !== '') {
            $qb->andWhere('t.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }
        $qb->orderBy('t.name', 'ASC');
        $qb->setMaxResults($limit);

        return array_column($qb->getQuery()->getResult(), 'name');
    }
}
