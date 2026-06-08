<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Sovic\Common\Entity\Trait\CreatedAtTrait;
use Sovic\Common\Entity\Trait\IdentityColumnTrait;
use UserBundle\Entity\Trait\CreatorTrait;

#[Index(name: 'entity_index', columns: ['entity', 'entity_id'])]
#[Index(name: 'relation_entity_index', columns: ['relation_entity', 'relation_entity_id'])]
#[Entity]
class Changelog
{
    use CreatedAtTrait;
    use CreatorTrait;
    use IdentityColumnTrait;

    #[Column(type: Types::STRING, length: 255)]
    private string $entity;

    #[Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    private ?string $relationEntity = null;

    #[Column(type: Types::STRING, length: 255)]
    private string $entityId;

    #[Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    private ?string $relationEntityId = null;

    #[Column(type: Types::SMALLINT, enumType: ChangelogActionId::class)]
    private ChangelogActionId $action;

    #[Column(type: Types::JSON, length: 65535)]
    private array $changes;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function setEntityId(string $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function getAction(): ChangelogActionId
    {
        return $this->action;
    }

    public function setAction(ChangelogActionId $action): void
    {
        $this->action = $action;
    }

    public function getRelationEntity(): ?string
    {
        return $this->relationEntity;
    }

    public function setRelationEntity(?string $relationEntity): void
    {
        $this->relationEntity = $relationEntity;
    }

    public function getRelationEntityId(): ?string
    {
        return $this->relationEntityId;
    }

    public function setRelationEntityId(?string $relationEntityId): void
    {
        $this->relationEntityId = $relationEntityId;
    }

    public function getChanges(): array
    {
        return $this->changes;
    }

    public function setChanges(array $changes): void
    {
        $this->changes = $changes;
    }
}
