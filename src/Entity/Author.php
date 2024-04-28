<?php

namespace Sovic\Cms\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'author')]
#[ORM\Index(columns: ['surname'], name: 'surname', options: ['lengths' => [191]])]
#[ORM\Index(columns: ['project_id'], name: 'project_id')]
#[ORM\Entity]
class Author
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Project $project;

    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    protected string $name;

    #[ORM\Column(name: 'surname', type: 'string', length: 255, nullable: false)]
    protected string $surname;

    #[ORM\Column(name: 'short_description', type: 'string', length: 1000, nullable: true, options: ['default' => null])]
    protected ?string $shortDescription;

    #[ORM\Column(name: 'user_id', type: 'integer', nullable: true, options: ['default' => null])]
    protected ?int $userId;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\OneToOne(targetEntity: User::class)]
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
