<?php

namespace SovicCms\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Post
 *
 * @ORM\Table(name="posts")
 * @ORM\Entity(repositoryClass="SovicCms\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected string $name;

    /**
     * @ORM\Column(name="raw_id", type="string", length=255, nullable=true)
     */
    protected string $rawID;

    /**
     * @ORM\Column(name="head_title", type="string", length=255, nullable=true)
     */
    protected string $headTitle;

    /**
     * @ORM\Column(name="meta_description", type="string", length=255, nullable=true)
     */
    protected ?string $metaDescription;

    /**
     * @ORM\Column(name="meta_keywords", type="string", length=255, nullable=true)
     */
    protected ?string $metaKeywords;

    /**
     * @ORM\Column(name="heading", type="string", length=255, nullable=true)
     */
    protected string $heading;

    /**
     * @ORM\Column(name="subtitle", type="string", length=255, nullable=true)
     */
    protected string $subtitle;

    /**
     * @ORM\Column(name="perex", length=65535, nullable=true)
     */
    protected string $perex;

    /**
     * @ORM\Column(name="content", length=4294967295, nullable=true)
     */
    protected string $content;

    /**
     * @ORM\Column(name="sequence", type="integer")
     */
    protected int $sequence;

    /**
     * @ORM\Column(name="public", type="boolean")
     */
    protected bool $public;

    /**
     * @ORM\Column(name="lang", type="string", length=5, nullable=true, options={"default"="cs"})
     */
    protected string $lang;

    /**
     * @ORM\Column(name="group_id", type="integer")
     */
    protected int $groupID;

    /**
     * @ORM\Column(name="published", type="integer", nullable=true, options={"default"="NULL"})
     */
    protected ?int $published;

    /**
     * @ORM\Column(name="created", type="integer")
     */
    protected int $created;

    /**
     * @ORM\Column(name="title", type="integer")
     */
    protected int $title;

    /**
     * @ORM\Column(name="import_id", type="integer")
     */
    protected int $importID;

    /**
     * @ORM\Column(name="import_service", type="string", length=50, nullable=true)
     */
    protected string $importService;

    /**
     * @ORM\Column(name="infobox", length=65535, nullable=true)
     */
    protected string $infoBox;

    /**
     * @ORM\Column(name="signature", type="string", length=1024, nullable=true)
     */
    protected string $signature;

    /**
     * @ORM\Column(name="modified", type="integer")
     */
    protected int $modified;

    /**
     * @ORM\Column(name="publishers_id", type="integer")
     */
    protected int $publishersID;

    /**
     * @ORM\Column(name="postsauthors_id", type="integer")
     */
    protected int $postsAuthorsID;

    /**
     * @ORM\Column(name="gallery", type="boolean")
     */
    protected bool $gallery;

    /**
     * @ORM\Column(name="media_id", type="integer")
     */
    protected int $mediaID;

    /**
     * @ORM\Column(name="authorsusers_id", type="integer", nullable=true, options={"default"="NULL"})
     */
    protected ?int $authorsUsersID;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getRawID(): string
    {
        return $this->rawID;
    }

    public function setRawID(string $rawID): void
    {
        $this->rawID = $rawID;
    }

    public function getHeadTitle(): string
    {
        return $this->headTitle;
    }

    public function setHeadTitle(string $headTitle): void
    {
        $this->headTitle = $headTitle;
    }

    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaKeywords(): ?string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(?string $metaKeywords): void
    {
        $this->metaKeywords = $metaKeywords;
    }

    public function getHeading(): string
    {
        return $this->heading;
    }

    public function setHeading(string $heading): void
    {
        $this->heading = $heading;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getPerex(): string
    {
        return $this->perex;
    }

    public function setPerex(string $perex): void
    {
        $this->perex = $perex;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getGroupID(): int
    {
        return $this->groupID;
    }

    public function setGroupID(int $groupID): void
    {
        $this->groupID = $groupID;
    }

    public function getPublished(): ?DateTimeImmutable
    {
        return $this->published ? (new DateTimeImmutable())->setTimestamp($this->published) : null;
    }

    public function setPublished(?DateTimeImmutable $published): void
    {
        $this->published = $published ? $published->getTimestamp() : null;
    }

    public function getCreated(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->setTimestamp($this->created);
    }

    public function setCreated(DateTimeImmutable $created): void
    {
        $this->created = $created->getTimestamp();
    }

    public function getTitle(): int
    {
        return $this->title;
    }

    public function setTitle(int $title): void
    {
        $this->title = $title;
    }

    public function getImportID(): int
    {
        return $this->importID;
    }

    public function setImportID(int $importID): void
    {
        $this->importID = $importID;
    }

    public function getImportService(): string
    {
        return $this->importService;
    }

    public function setImportService(string $importService): void
    {
        $this->importService = $importService;
    }

    public function getInfoBox(): string
    {
        return $this->infoBox;
    }

    public function setInfoBox(string $infoBox): void
    {
        $this->infoBox = $infoBox;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): void
    {
        $this->signature = $signature;
    }

    public function getModified(): int
    {
        return $this->modified;
    }

    public function setModified(int $modified): void
    {
        $this->modified = $modified;
    }

    public function getPublishersID(): int
    {
        return $this->publishersID;
    }

    public function setPublishersID(int $publishersID): void
    {
        $this->publishersID = $publishersID;
    }

    public function getPostsAuthorsID(): int
    {
        return $this->postsAuthorsID;
    }

    public function setPostsAuthorsID(int $postsAuthorsID): void
    {
        $this->postsAuthorsID = $postsAuthorsID;
    }

    public function isGallery(): bool
    {
        return $this->gallery;
    }

    public function setGallery(bool $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getMediaID(): int
    {
        return $this->mediaID;
    }

    public function setMediaID(int $mediaID): void
    {
        $this->mediaID = $mediaID;
    }

    public function getAuthorsUsersID(): ?int
    {
        return $this->authorsUsersID;
    }

    public function setAuthorsUsersID(?int $authorsUsersID): void
    {
        $this->authorsUsersID = $authorsUsersID;
    }
}
