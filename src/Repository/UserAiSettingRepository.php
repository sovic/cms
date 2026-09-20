<?php

namespace Sovic\Cms\Repository;

use Doctrine\ORM\EntityRepository;
use Sovic\Cms\Entity\UserAiSetting;
use UserBundle\User\UserEntityInterface;

/**
 * @method UserAiSetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAiSetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAiSetting[]    findAll()
 * @method UserAiSetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAiSettingRepository extends EntityRepository
{
    public function findOneByUser(UserEntityInterface $user): ?UserAiSetting
    {
        return $this->findOneBy(['user' => $user]);
    }
}
