<?php

namespace Sovic\Cms\Controller\Trait;

use Symfony\Component\Security\Core\Authorization\AccessDecision;

trait ControllerAccessTrait
{
    protected function getEmailAccessDecision(string $attribute): void
    {
        $accessDecision = new AccessDecision();
        $accessDecision->isGranted = $this->isAttributeGranted($attribute);

        if (!$accessDecision->isGranted) {
            $e = $this->createAccessDeniedException($accessDecision->getMessage());
            $e->setAttributes([$attribute]);
            $e->setAccessDecision($accessDecision);

            throw $e;
        }
    }
}
