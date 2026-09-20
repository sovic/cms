<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Repository\AiConversationRepository;
use Sovic\Common\Entity\Trait\CreatedAtTrait;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;
use Sovic\Common\Entity\Trait\UpdatedAtTrait;
use UserBundle\Entity\User;
use UserBundle\User\UserEntityInterface;

#[Table(name: 'ai_conversation')]
#[Index(name: 'page_id_user_id', columns: ['page_id', 'user_id'])]
#[Entity(repositoryClass: AiConversationRepository::class)]
class AiConversation
{
    use CreatedAtTrait;
    use IdentityColumnTrait;
    use UpdatedAtTrait;

    #[ManyToOne(targetEntity: Page::class)]
    #[JoinColumn(name: 'page_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Page $page;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private UserEntityInterface $user;

    public function __construct(Page $page, UserEntityInterface $user)
    {
        $this->page = $page;
        $this->user = $user;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getPage(): Page
    {
        return $this->page;
    }

    public function getUser(): UserEntityInterface
    {
        return $this->user;
    }
}
