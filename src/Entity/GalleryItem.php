<?php

namespace SovicCms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SovicCms\Entity\GalleryItem
 *
 * @ORM\Table(name="gallery_item")
 * @ORM\Entity
 */
class GalleryItem
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="gallery_id", type="integer")
     */
    protected int $galleryId;

    /**
     * @ORM\Column(name="file", type="string", length=50, nullable=true, options={"default"=NULL})
     */
    protected ?string $extension;

    /**
     * @ORM\Column(name="description", type="text", length=65535, nullable=true, options={"default"=NULL})
     */
    protected ?string $description;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=true, options={"default"=NULL})
     */
    protected ?string $name;

    /**
     * @ORM\Column(name="timestamp", type="integer", nullable=true, options={"default"=NULL})
     */
    protected ?int $timestamp;

    /**
     * @ORM\Column(name="sequence", type="integer", nullable=true, options={"default"=NULL})
     */
    protected ?int $sequence;

    /**
     * @ORM\Column(name="width", type="integer", nullable=false, options={"default"="0"})
     */
    protected int $width = 0;

    /**
     * @ORM\Column(name="height", type="integer", nullable=false, options={"default"="0"})
     */
    protected int $height = 0;

    /**
     * @ORM\Column(name="model", type="string", length=100)
     */
    protected string $model;

    /**
     * @ORM\Column(name="model_id", type="integer")
     */
    protected int $modelId;

    /**
     * @ORM\Column(name="is_processed", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isProcessed = false;

    /**
     * @ORM\Column(name="is_cover", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isCover = false;

    /**
     * @ORM\Column(name="is_optimized", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isOptimized = false;

    /**
     * @ORM\Column(name="is_temp", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isTemp = false;

    /**
     * @ORM\Column(name="is_hero", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isHero = false;

    /**
     * @ORM\Column(name="is_hero_mobile", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isHeroMobile;

    /**
     * @ORM\Column(name="is_meta_image", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $isMetaImage = false;

    /**
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="galleryItems")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected Gallery $gallery;

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

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function setTimestamp(?int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(?int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): void
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
}
