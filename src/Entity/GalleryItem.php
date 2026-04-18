<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Sovic\Cms\Repository\GalleryItemRepository;

#[ORM\Table(name: 'gallery_item')]
#[ORM\Entity(repositoryClass: GalleryItemRepository::class)]
class GalleryItem
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\Column(name: 'gallery_id', type: 'integer')]
    protected int $galleryId;

    #[ORM\Column(name: 'extension', type: 'string', length: 50, nullable: true, options: ['default' => null])]
    protected ?string $extension = null;

    #[ORM\Column(name: 'description', type: 'text', length: 65535, nullable: true, options: ['default' => null])]
    protected ?string $description = null;

    #[ORM\Column(name: 'filesize', type: 'integer', nullable: true, options: ['default' => null])]
    protected ?int $filesize = null;

    #[ORM\Column(name: 'name', type: 'string', length: 100, nullable: true, options: ['default' => null])]
    protected ?string $name = null;

    #[ORM\Column(name: 'sequence', type: 'integer', nullable: true, options: ['default' => null])]
    protected ?int $sequence;

    #[ORM\Column(name: 'width', type: 'integer', nullable: true, options: ['default' => null])]
    protected ?int $width = null;

    #[ORM\Column(name: 'height', type: 'integer', nullable: true, options: ['default' => null])]
    protected ?int $height = null;

    #[ORM\Column(name: 'model', type: 'string', length: 100)]
    protected string $model;

    #[ORM\Column(name: 'model_id', type: 'integer')]
    protected int $modelId;

    #[ORM\Column(name: 'is_processed', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $isProcessed = false;

    #[ORM\Column(name: 'is_cover', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $isCover = false;

    #[ORM\Column(name: 'is_optimized', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $isOptimized = false;

    #[ORM\Column(name: 'is_temp', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $isTemp = false;

    #[ORM\Column(name: 'is_hero', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $isHero = false;

    #[ORM\Column(name: 'is_hero_mobile', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $isHeroMobile = false;

    #[ORM\Column(name: 'is_meta_image', type: 'boolean', nullable: false, options: ['default' => false])]
    protected bool $isMetaImage = false;

    #[ORM\JoinColumn(name: 'gallery_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'galleryItems')]
    protected Gallery $gallery;

    #[ORM\Column(name: 'create_date', type: 'datetime_immutable', nullable: false)]
    protected DateTimeImmutable $createDate;

    #[ORM\Column(name: 'path', type: 'string', length: 255, nullable: true, options: ['default' => null])]
    protected ?string $path = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getGalleryId(): int
    {
        return $this->galleryId;
    }

    public function setGalleryId(int $galleryId): void
    {
        $this->galleryId = $galleryId;
    }

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(?string $extension): void
    {
        $this->extension = $extension;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(?int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(?string $model): void
    {
        $this->model = $model;
    }

    public function getModelId(): ?int
    {
        return $this->modelId;
    }

    public function setModelId(?int $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function isProcessed(): bool
    {
        return $this->isProcessed;
    }

    public function setIsProcessed(bool $isProcessed): void
    {
        $this->isProcessed = $isProcessed;
    }

    public function isCover(): bool
    {
        return $this->isCover;
    }

    public function setIsCover(bool $isCover): void
    {
        $this->isCover = $isCover;
    }

    public function isOptimized(): bool
    {
        return $this->isOptimized;
    }

    public function setIsOptimized(bool $isOptimized): void
    {
        $this->isOptimized = $isOptimized;
    }

    public function isTemp(): bool
    {
        return $this->isTemp;
    }

    public function setIsTemp(bool $isTemp): void
    {
        $this->isTemp = $isTemp;
    }

    public function isHero(): bool
    {
        return $this->isHero;
    }

    public function setIsHero(bool $isHero): void
    {
        $this->isHero = $isHero;
    }

    public function isHeroMobile(): bool
    {
        return $this->isHeroMobile;
    }

    public function setIsHeroMobile(bool $isHeroMobile): void
    {
        $this->isHeroMobile = $isHeroMobile;
    }

    public function isMetaImage(): bool
    {
        return $this->isMetaImage;
    }

    public function setIsMetaImage(bool $isMetaImage): void
    {
        $this->isMetaImage = $isMetaImage;
    }

    public function getGallery(): Gallery
    {
        return $this->gallery;
    }

    public function setGallery(Gallery $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTimeImmutable $createDate): void
    {
        $this->createDate = $createDate;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): void
    {
        $this->path = $path;
    }

    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    public function setFilesize(?int $filesize): void
    {
        $this->filesize = $filesize;
    }
}
