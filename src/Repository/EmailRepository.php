<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Email\EmailIdInterface;
use Sovic\Cms\Entity\Email;

/**
 * @method Email|null find($id, $lockMode = null, $lockVersion = null)
 * @method Email|null findOneBy(array $criteria, array $orderBy = null)
 * @method Email[]    findAll()
 * @method Email[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailRepository extends EntityRepository
{
    public function findBySearch(string $search, int $offset, int $limit): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e');
        $qb->from(Email::class, 'e');
        $qb->where('e.name LIKE :search');
        $qb->setParameter('search', '%' . $search . '%');

        $qb->setFirstResult($offset);
        $qb->setMaxResults($limit);

        $qb->orderBy('e.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findByEmailId(EmailIdInterface $emailId): ?Email
    {
        return $this->findOneBy(
            [
                'emailId' => $emailId->getId(),
            ]
        );
    }
}
