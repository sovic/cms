<?php

namespace Sovic\Cms\Email;

use Sovic\Common\DataList\AbstractSearchRequest;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailSearchRequest extends AbstractSearchRequest
{
    private ?UserInterface $user = null;

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): void
    {
        $this->user = $user;
    }

    public function toArray(): array
    {
        return [
            'limit' => $this->getLimit(),
            'page' => $this->getPage(),
            'search' => $this->getSearch() ?? '',
        ];
    }
}