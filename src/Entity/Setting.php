<?php

namespace Sovic\Cms\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Table(name: 'setting')]
#[ORM\Index(columns: ['project_id'], name: 'project_id')]
#[ORM\Entity]
class Setting
{
    private const TYPE_STRING = Types::STRING;
    private const TYPE_INT = 'int'; // number
    private const TYPE_BOOL = 'bool'; // 0|1
    private const TYPE_ARRAY = 'array'; // array of strings, each line one field

    private const TYPES = [
        self::TYPE_ARRAY,
        self::TYPE_BOOL,
        self::TYPE_INT,
        self::TYPE_STRING,
    ];

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Project $project;

    #[ORM\Column(name: '`group`', type: Types::STRING, length: 100, nullable: false)]
    protected string $group;

    #[ORM\Column(name: 'key', type: Types::STRING, length: 100, nullable: false)]
    protected string $key;

    #[ORM\Column(name: 'value', type: Types::TEXT, length: 65535, nullable: false)]
    protected string $value;

    #[ORM\Column(name: 'description', type: Types::TEXT, length: 65535, nullable: false)]
    protected string $description;

    #[ORM\Column(name: 'type', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $type;
    #[ORM\Column(name: 'is_template_enabled', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $isTemplateEnabled = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        if ($type !== null && !in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('invalid type');
        }
        $this->type = $type;
    }

    public function isTemplateEnabled(): bool
    {
        return $this->isTemplateEnabled;
    }

    public function setIsTemplateEnabled(bool $isTemplateEnabled): void
    {
        $this->isTemplateEnabled = $isTemplateEnabled;
    }
}
