<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Cms\Repository\UserAiSettingRepository;
use Sovic\Common\Entity\Trait\CreatedAtTrait;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;
use Sovic\Common\Entity\Trait\UpdatedAtTrait;
use UserBundle\Entity\User;
use UserBundle\User\UserEntityInterface;

/**
 * Per-user AI assistant settings, the API key is stored encrypted (see ApiKeyEncryptor).
 */
#[Table(name: 'user_ai_setting')]
#[Entity(repositoryClass: UserAiSettingRepository::class)]
class UserAiSetting
{
    use CreatedAtTrait;
    use IdentityColumnTrait;
    use UpdatedAtTrait;

    #[OneToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: true, nullable: false, onDelete: 'CASCADE')]
    private UserEntityInterface $user;

    #[Column(name: 'api_key_encrypted', type: Types::TEXT, nullable: true, options: ['default' => null])]
    private ?string $apiKeyEncrypted = null;

    #[Column(name: 'api_key_hint', type: Types::STRING, length: 4, nullable: true, options: ['default' => null])]
    private ?string $apiKeyHint = null;

    #[Column(name: 'model', type: Types::STRING, length: 64, nullable: true, options: ['default' => null])]
    private ?string $model = null;

    public function __construct(UserEntityInterface $user)
    {
        $this->user = $user;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUser(): UserEntityInterface
    {
        return $this->user;
    }

    public function getApiKeyEncrypted(): ?string
    {
        return $this->apiKeyEncrypted;
    }

    public function setApiKeyEncrypted(?string $apiKeyEncrypted): void
    {
        $this->apiKeyEncrypted = $apiKeyEncrypted;
    }

    public function getApiKeyHint(): ?string
    {
        return $this->apiKeyHint;
    }

    public function setApiKeyHint(?string $apiKeyHint): void
    {
        $this->apiKeyHint = $apiKeyHint;
    }

    public function hasApiKey(): bool
    {
        return !empty($this->apiKeyEncrypted);
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }
}
