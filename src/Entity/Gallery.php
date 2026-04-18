<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\PersistentCollection;

#[Table(name: 'gallery')]
#[Index(name: 'model_model_id', columns: ['model', 'model_id'])]
#[Index(name: 'model_model_id_name', columns: ['model', 'model_id', 'name'])]
#[Entity]
class Gallery
{
    #[Column(name: 'id', type: 'integer')]
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[Column(name: 'session_id', type: 'string', length: 32, nullable: true, options: ['default' => 'NULL'])]
    protected ?string $sessionId;

    #[Column(name: 'model', type: 'string', length: 100, nullable: false)]
    protected string $model;

    #[Column(name: 'model_id', type: 'integer', nullable: false)]
    protected int $modelId;

    #[Column(name: 'name', type: 'string', length: 100, nullable: false)]
    protected string $name;

    #[Column(name: 'timestamp', type: 'integer', nullable: true, options: ['default' => 'NULL'])]
    protected ?int $timestamp;

    #[Column(name: 'users_id', type: 'integer', nullable: true, options: ['default' => 'NULL'])]
    protected ?int $usersId;

    #[Column(name: 'is_processed', type: 'boolean', nullable: false, options: ['default' => '0'])]
    protected bool $isProcessed = false;

    /**
     * @var GalleryItem[]|PersistentCollection
     */
    #[OneToMany(targetEntity: GalleryItem::class, mappedBy: 'gallery', fetch: 'LAZY')]
    protected mixed $galleryItems;

    #[Column(name: 'path', type: 'string', length: 255, nullable: true, options: ['default' => null])]
    protected ?string $path = null;

    #[Column(name: 'create_date', type: 'datetime_immutable', nullable: false)]
    protected DateTimeImmutable $createDate;

    #[Column(name: 'is_download_enabled', type: 'boolean', nullable: false, options: ['default' => '0'])]
    protected bool $isDownloadEnabled = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(?string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModelId(): int
    {
        return $this->modelId;
    }

    public function setModelId(int $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getUsersId(): ?int
    {
        return $this->usersId;
    }

    public function setUsersId(?int $usersId): void
    {
        $this->usersId = $usersId;
    }

    public function isIsProcessed(): bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): void
    {
        $this->isProcessed = $isProcessed;
    }

    public function getGalleryItems(): mixed
    {
        return $this->galleryItems;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTimeImmutable $createDate): void
    {
        $this->createDate = $createDate;
    }

    public function isDownloadEnabled(): bool
    {
        return $this->isDownloadEnabled;
    }

    public function setIsDownloadEnabled(bool $isDownloadEnabled): void
    {
        $this->isDownloadEnabled = $isDownloadEnabled;
    }
}
