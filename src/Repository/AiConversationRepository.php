<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\AiConversation;
use Sovic\Cms\Entity\Page;
use UserBundle\User\UserEntityInterface;

/**
 * @method AiConversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method AiConversation|null findOneBy(array $criteria, array $orderBy = null)
 * @method AiConversation[]    findAll()
 * @method AiConversation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AiConversationRepository extends EntityRepository
{
    public function findLatestByPageAndUser(Page $page, UserEntityInterface $user): ?AiConversation
    {
        $qb = $this->createQueryBuilder('c');
        $qb->andWhere('c.page = :page');
        $qb->andWhere('c.user = :user');
        $qb->setParameter('page', $page);
        $qb->setParameter('user', $user);
        $qb->orderBy('c.id', 'DESC');
        $qb->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
