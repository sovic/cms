<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sovic\Cms\Repository\PostRepository;

#[ORM\Table(name: 'post')]
#[ORM\Index(columns: ['url_id', 'public'], name: 'public_post')]
#[ORM\Index(columns: ['published'], name: 'published')]
#[ORM\Index(columns: ['project_id'], name: 'project_id')]
#[ORM\UniqueConstraint(name: 'project_id_url_id', columns: ['project_id', 'url_id'])]
#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    public int $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Project $project;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    protected string $name;

    #[ORM\Column(name: 'url_id', type: Types::STRING, length: 255, nullable: false)]
    public string $urlId;

    #[ORM\Column(name: 'meta_title', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaTitle = null;

    #[ORM\Column(name: 'meta_description', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaDescription = null;

    #[ORM\Column(name: 'meta_keywords', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaKeywords = null;

    #[ORM\Column(name: 'heading', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $heading = null;

    #[ORM\Column(name: 'subtitle', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $subtitle = null;

    #[ORM\Column(name: 'perex', type: Types::TEXT, length: 16383, nullable: true, options: ['default' => null])]
    protected ?string $perex = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, length: 4294967295, nullable: true, options: ['default' => null])]
    protected ?string $content;

    #[ORM\Column(name: '`index`', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $index = null;

    #[ORM\Column(name: 'public', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $public = false;

    #[ORM\Column(name: 'lang', type: Types::STRING, length: 5, nullable: true, options: ['default' => null])]
    protected ?string $lang;

    #[ORM\Column(name: 'group_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $groupId = null;

    #[ORM\Column(name: 'published', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $publishDate = null;

    #[ORM\Column(name: 'created', type: Types::INTEGER)]
    protected int $created;

    #[ORM\Column(name: 'create_date', type: Types::DATETIME_IMMUTABLE)]
    protected DateTimeImmutable $createDate;

    #[ORM\Column(name: 'title', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $title = null;

    #[ORM\Column(name: 'import_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $importId = null;

    #[ORM\Column(name: 'import_service', type: Types::STRING, length: 50, nullable: true, options: ['default' => null])]
    protected ?string $importService = null;

    #[ORM\Column(name: 'infobox', type: Types::TEXT, length: 1024, nullable: true)]
    protected ?string $infoBox = null;

    #[ORM\Column(name: 'signature', type: Types::STRING, length: 1024, nullable: true, options: ['default' => null])]
    protected ?string $signature = null;

    #[ORM\Column(name: 'modified', type: Types::INTEGER, options: ['default' => 0])]
    protected int $modified = 0;

    #[ORM\Column(name: 'last_modified_date', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    protected ?DateTimeImmutable $lastModifiedDate = null;

    #[ORM\Column(name: 'publishers_id', type: Types::INTEGER, options: ['default' => 0])]
    protected int $publishersId = 0;

    #[ORM\Column(name: 'postsauthors_id', type: Types::INTEGER, options: ['default' => 0])]
    protected int $postsAuthorsId = 0;

    #[ORM\Column(name: 'gallery', type: Types::BOOLEAN, options: ['default' => false])]
    protected bool $gallery = false;

    #[ORM\Column(name: 'media_id', type: Types::INTEGER, options: ['default' => 0])]
    protected int $mediaId = 0;

    #[ORM\Column(name: 'authorsusers_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $authorsUsersId = null;

    #[ORM\Column(name: 'secret', type: Types::STRING, length: 10, nullable: true)]
    protected ?string $secret = null;

    #[ORM\Column(name: 'is_featured', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $isFeatured = false;

    #[ORM\Column(name: 'is_gallery_enabled', type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
    protected bool $isGalleryEnabled = true;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setUrlId(string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
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

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(?string $heading): void
    {
        $this->heading = $heading;
    }

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(?string $subtitle): void
    {
        $this->subtitle = $subtitle;
    }

    public function getPerex(): ?string
    {
        return $this->perex;
    }

    public function setPerex(?string $perex): void
    {
        $this->perex = $perex;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getIndex(): ?int
    {
        return $this->index;
    }

    public function setIndex(?int $index): void
    {
        $this->index = $index;
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

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(?int $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getPublishDate(): ?DateTimeImmutable
    {
        return $this->publishDate ? (new DateTimeImmutable())->setTimestamp($this->publishDate) : null;
    }

    public function setPublishDate(?DateTimeImmutable $publishDate): void
    {
        $this->publishDate = $publishDate?->getTimestamp();
    }

    public function getCreated(): DateTimeImmutable
    {
        return (new DateTimeImmutable())->setTimestamp($this->created);
    }

    public function setCreated(DateTimeImmutable $created): void
    {
        $this->created = $created->getTimestamp();
    }

    public function getTitle(): ?int
    {
        return $this->title;
    }

    public function setTitle(?int $title): void
    {
        $this->title = $title;
    }

    public function getModified(): int
    {
        return $this->modified;
    }

    public function setModified(int $modified): void
    {
        $this->modified = $modified;
    }

    public function getPublishersId(): int
    {
        return $this->publishersId;
    }

    public function setPublishersId(int $publishersId): void
    {
        $this->publishersId = $publishersId;
    }

    public function getPostsAuthorsId(): int
    {
        return $this->postsAuthorsId;
    }

    public function setPostsAuthorsId(int $postsAuthorsId): void
    {
        $this->postsAuthorsId = $postsAuthorsId;
    }

    public function isGallery(): bool
    {
        return $this->gallery;
    }

    public function setGallery(bool $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getMediaId(): int
    {
        return $this->mediaId;
    }

    public function setMediaId(int $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    public function getAuthorsUsersId(): ?int
    {
        return $this->authorsUsersId;
    }

    public function setAuthorsUsersId(?int $authorsUsersId): void
    {
        $this->authorsUsersId = $authorsUsersId;
    }

    public function getImportId(): ?int
    {
        return $this->importId;
    }

    public function setImportId(?int $importId): void
    {
        $this->importId = $importId;
    }

    public function getImportService(): ?string
    {
        return $this->importService;
    }

    public function setImportService(?string $importService): void
    {
        $this->importService = $importService;
    }

    public function getInfoBox(): ?string
    {
        return $this->infoBox;
    }

    public function setInfoBox(?string $infoBox): void
    {
        $this->infoBox = $infoBox;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): void
    {
        $this->signature = $signature;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
    }

    public function isFeatured(): bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): void
    {
        $this->isFeatured = $isFeatured;
    }

    public function isGalleryEnabled(): bool
    {
        return $this->isGalleryEnabled;
    }

    public function setIsGalleryEnabled(bool $isGalleryEnabled): void
    {
        $this->isGalleryEnabled = $isGalleryEnabled;
    }

    public function getCreateDate(): DateTimeImmutable
    {
        return $this->createDate;
    }

    public function setCreateDate(DateTimeImmutable $createDate): void
    {
        $this->createDate = $createDate;
    }

    public function getLastModifiedDate(): ?DateTimeImmutable
    {
        return $this->lastModifiedDate;
    }

    public function setLastModifiedDate(?DateTimeImmutable $lastModifiedDate): void
    {
        $this->lastModifiedDate = $lastModifiedDate;
    }
}

