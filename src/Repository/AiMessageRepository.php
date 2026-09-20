<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\AiConversation;
use Sovic\Cms\Entity\AiMessage;

/**
 * @method AiMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method AiMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method AiMessage[]    findAll()
 * @method AiMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AiMessageRepository extends EntityRepository
{
    /**
     * @return AiMessage[] oldest first
     */
    public function findByConversation(AiConversation $conversation): array
    {
        $qb = $this->createQueryBuilder('m');
        $qb->andWhere('m.conversation = :conversation');
        $qb->setParameter('conversation', $conversation);
        $qb->orderBy('m.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return AiMessage[] the last $limit messages, oldest first
     */
    public function findLastByConversation(AiConversation $conversation, int $limit): array
    {
        if ($limit <= 0) {
            return [];
        }

        $qb = $this->createQueryBuilder('m');
        $qb->andWhere('m.conversation = :conversation');
        $qb->setParameter('conversation', $conversation);
        $qb->orderBy('m.id', 'DESC');
        $qb->setMaxResults($limit);

        return array_reverse($qb->getQuery()->getResult());
    }
}
