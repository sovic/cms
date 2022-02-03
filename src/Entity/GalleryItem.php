<?php

namespace SovicCms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SovicCms\Entity\GalleryItem
 *
 * @ORM\Table(name="galleriesitems")
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
     * @ORM\Column(name="file", type="string", length=50, nullable=true, options={"default"="NULL"})
     */
    protected ?string $extension;

    /**
     * @ORM\Column(name="description", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    protected ?string $description;

    /**
     * @ORM\Column(name="name", type="string", length=100, nullable=true, options={"default"="NULL"})
     */
    protected ?string $name;

    /**
     * @ORM\Column(name="timestamp", type="integer", nullable=true, options={"default"="NULL"})
     */
    protected ?int $timestamp;

    /**
     * @ORM\Column(name="sequence", type="integer", nullable=true, options={"default"="NULL"})
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
     * @ORM\Column(name="tagslist", type="string", length=255, nullable=true, options={"default"="NULL"})
     */
    protected ?string $tagsList;

    /**
     * @ORM\Column(name="model", type="string", length=100)
     */
    protected string $model;

    /**
     * @ORM\Column(name="model_id", type="integer")
     */
    protected int $modelId;

    /**
     * @ORM\Column(name="processed", type="boolean", nullable=true, options={"default"="0"})
     */
    protected ?bool $processed = false;

    /**
     * @ORM\Column(name="generate", type="boolean", nullable=true, options={"default"="0"})
     */
    protected ?bool $generate = false;

    /**
     * @ORM\Column(name="title", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $title = false;

    /**
     * @ORM\Column(name="tagssearch", type="text", length=65535, nullable=true, options={"default"="NULL"})
     */
    protected ?string $tagsSearch;

    /**
     * @ORM\Column(name="optimized", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $optimized = false;

    /**
     * @ORM\Column(name="temp", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $temp = false;

    /**
     * @ORM\Column(name="hero", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $hero = false;

    /**
     * @ORM\Column(name="hero_mobile", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $heroMobile;

    /**
     * @ORM\Column(name="meta_image", type="boolean", nullable=false, options={"default"="0"})
     */
    protected bool $metaImage = false;

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

    public function getTagsList(): ?string
    {
        return $this->tagsList;
    }

    public function setTagsList(?string $tagsList): void
    {
        $this->tagsList = $tagsList;
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
        return (bool) $this->processed;
    }

    public function setProcessed(bool $processed): void
    {
        $this->processed = $processed;
    }

    public function isGenerate(): bool
    {
        return (bool) $this->generate;
    }

    public function setGenerate(bool $generate): void
    {
        $this->generate = $generate;
    }

    public function isTitle(): bool
    {
        return $this->title;
    }

    public function setTitle(bool $title): void
    {
        $this->title = $title;
    }

    public function getTagsSearch(): ?string
    {
        return $this->tagsSearch;
    }

    public function setTagsSearch(?string $tagsSearch): void
    {
        $this->tagsSearch = $tagsSearch;
    }

    public function isOptimized(): bool
    {
        return $this->optimized;
    }

    public function setOptimized(bool $optimized): void
    {
        $this->optimized = $optimized;
    }

    public function isTemp(): bool
    {
        return $this->temp;
    }

    public function setTemp(bool $temp): void
    {
        $this->temp = $temp;
    }

    public function isHero(): bool
    {
        return $this->hero;
    }

    public function setHero(bool $hero): void
    {
        $this->hero = $hero;
    }

    public function isHeroMobile(): bool
    {
        return $this->heroMobile;
    }

    public function setHeroMobile(bool $heroMobile): void
    {
        $this->heroMobile = $heroMobile;
    }

    public function isMetaImage(): bool
    {
        return $this->metaImage;
    }

    public function setMetaImage(bool $metaImage): void
    {
        $this->metaImage = $metaImage;
    }

    public function getGallery(): Gallery
    {
        return $this->gallery;
    }
}
