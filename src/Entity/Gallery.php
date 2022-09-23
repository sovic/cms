<?php

namespace SovicCms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SovicCms\Entity\Gallery
 *
 * @ORM\Table(
 *     name="gallery",
 *     indexes={
 *         @ORM\Index(name="model_model_id", columns={"model","model_id"}),
 *         @ORM\Index(name="model_model_id_name", columns={"model","model_id","name"})
 *     }
 * )
 * @ORM\Entity
 */
class Gallery
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="session_id", type="string", length=32, nullable=true, options={"default"="NULL"})
     */
    protected ?string $sessionID;

    /**
     * @ORM\Column(name="model", type="string", length=100)
     */
    protected string $model;

    /**
     * @ORM\Column(name="model_id", type="integer")
     */
    protected int $modelId;

    /**
     * @ORM\Column(name="name", type="string", length=100)
     */
    protected string $name;

    /**
     * @ORM\Column(name="timestamp", type="integer", nullable=true, options={"default"="NULL"})
     */
    protected ?int $timestamp;

    /**
     * @ORM\Column(name="users_id", type="integer", nullable=true, options={"default"="NULL"})
     */
    protected ?int $usersID;

    /**
     * @ORM\Column(name="processed", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $processed = false;

    /**
     * @var GalleryItem[]
     *
     * @ORM\OneToMany(targetEntity="GalleryItem", mappedBy="gallery")
     */
    protected array $galleryItems;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSessionID(): ?string
    {
        return $this->sessionID;
    }

    public function setSessionID(?string $sessionID): void
    {
        $this->sessionID = $sessionID;
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

    public function getUsersID(): ?int
    {
        return $this->usersID;
    }

    public function setUsersID(?int $usersID): void
    {
        $this->usersID = $usersID;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

    public function getGalleryItems(): array
    {
        return $this->galleryItems;
    }
}
