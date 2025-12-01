<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Sovic\Common\Entity\Project;
use UserBundle\Entity\User;

#[Table(name: 'author')]
#[Index(name: 'surname', columns: ['surname'], options: ['lengths' => [191]])]
#[Index(name: 'project_id', columns: ['project_id'])]
#[Entity]
class Author
{
    #[Column(name: 'id', type: Types::INTEGER)]
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ManyToOne(targetEntity: Project::class)]
    #[JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Project $project;

    #[Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    protected string $name;

    #[Column(name: 'surname', type: Types::STRING, length: 255, nullable: false)]
    protected string $surname;

    #[Column(name: 'short_description', type: Types::STRING, length: 1000, nullable: true, options: ['default' => null])]
    protected ?string $shortDescription;

    #[Column(name: 'user_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $userId;

    #[JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[OneToOne(targetEntity: User::class)]
    protected User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }


    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): void
    {
        $this->surname = $surname;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }
}
