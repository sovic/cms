<?php

namespace Sovic\Cms\Entity\Trait;

use DateTimeInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\PostUpdate;
use Doctrine\ORM\Mapping\PreUpdate;
use Sovic\Cms\Entity\Changelog;
use Sovic\Cms\Entity\ChangelogActionId;
use Sovic\Common\Entity\LoggableEntityInterface;
use UserBundle\User\UserEntityInterface;

trait LoggableEntityTrait
{
    protected ?Changelog $changelog = null;
    /** @var UserEntityInterface|null */
    protected mixed $operator = null;

    protected function getLoggableFields(): array
    {
        return [];
    }

    protected function getLoggableEntity(): string
    {
        return __CLASS__;
    }

    protected function getLoggableEntityId(): int
    {
        return $this->getIdentifier();
    }

    protected function getRelationEntity(): ?string
    {
        return null;
    }

    protected function getRelationEntityId(): ?int
    {
        return null;
    }

    #[PreUpdate]
    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $changes = $event->getEntityChangeSet();
        $loggableChanges = [];
        foreach ($this->getLoggableFields() as $field) {
            if (isset($changes[$field])) {
                if ($changes[$field][0] instanceof DateTimeInterface) {
                    $changes[$field][0] = $changes[$field][0]->format('Y-m-d H:i:s e');
                }
                if ($changes[$field][1] instanceof DateTimeInterface) {
                    $changes[$field][1] = $changes[$field][1]->format('Y-m-d H:i:s e');
                }
                if ($changes[$field][0] instanceof LoggableEntityInterface) {
                    $changes[$field][0] = $changes[$field][0]->getIdentifier();
                }
                if ($changes[$field][1] instanceof LoggableEntityInterface) {
                    $changes[$field][1] = $changes[$field][1]->getIdentifier();
                }

                $loggableChanges[$field] = $changes[$field];
            }
        }

        if (count($loggableChanges) > 0) {
            $changelog = new Changelog();
            $changelog->setEntity($this->getLoggableEntity());
            $changelog->setEntityId($this->getLoggableEntityId());
            $changelog->setRelationEntity($this->getRelationEntity());
            $changelog->setRelationEntityId($this->getRelationEntityId());
            $changelog->setChanges($loggableChanges);
            $changelog->setCreator($this->operator);
            $changelog->setAction(ChangelogActionId::Update);

            $this->changelog = $changelog;
        }
    }

    #[PostUpdate]
    public function postUpdate(PostUpdateEventArgs $event): void
    {
        if ($this->changelog) {
            $em = $event->getObjectManager();
            $em->persist($this->changelog);
            $em->flush();
        }
    }

    public function getOperator(): mixed
    {
        return $this->operator;
    }

    public function setOperator(mixed $operator): void
    {
        $this->operator = $operator;
    }
}
