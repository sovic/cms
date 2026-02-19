<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use RuntimeException;
use Sovic\Cms\Email\EmailIdInterface;
use Sovic\Cms\Email\EmailSearchRequest;
use Sovic\Cms\Entity\Email;
use Sovic\Common\DataList\Enum\VisibilityId;

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

    public function findBySearchRequest(EmailSearchRequest $searchRequest): array
    {
        $qb = $this->createSearchQueryBuilder($searchRequest);

        $qb->orderBy('e.createdAt', 'DESC');
        $qb->setFirstResult(($searchRequest->getPage() - 1) * $searchRequest->getLimit());
        $qb->setMaxResults($searchRequest->getLimit());

        return $qb->getQuery()->getResult();
    }

    public function countBySearchRequest(EmailSearchRequest $searchRequest): int
    {
        $qb = $this->createSearchQueryBuilder($searchRequest);

        $qb->select('COUNT(e.id)');

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    private function createSearchQueryBuilder(EmailSearchRequest $searchRequest): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e');
        $qb->from(Email::class, 'e');

        $search = $searchRequest->getSearch();
        if ($search) {
            $qb->where('e.name LIKE :search');
            $qb->setParameter('search', '%' . $search . '%');
        }
        if ($searchRequest->getUser()) {
            $qb->andWhere('e.creator = :creator');
            $qb->setParameter('creator', $searchRequest->getUser());
        }

        $visibilityId = $searchRequest->getVisibilityId();
        switch ($visibilityId) {
            case VisibilityId::Private:
                throw new RuntimeException('Not implemented yet.');
            case VisibilityId::Deleted:
                $qb->andWhere('e.deletedAt IS NOT NULL');
                break;
            case VisibilityId::All:
                // No additional criteria
                break;
            case VisibilityId::Public:
                $qb->andWhere('e.deletedAt IS NULL');
                break;
        }

        return $qb;
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
