<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\UserAiSetting;
use Sovic\Cms\Repository\UserAiSettingRepository;
use UserBundle\User\UserEntityInterface;

trait AiControllerTrait
{
    /**
     * AI assistant is available when current user has API key set and AI enabled (Admin AI settings).
     */
    private function isAiEnabled(EntityManagerInterface $em): bool
    {
        $user = $this->getUser();
        if (!$user instanceof UserEntityInterface) {
            return false;
        }

        /** @var UserAiSettingRepository $repo */
        $repo = $em->getRepository(UserAiSetting::class);

        return $repo->isAiAvailable($user);
    }
}
