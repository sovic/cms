<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Sovic\Cms\Repository\PostRepository;

#[Table(name: 'post')]
#[Index(columns: ['url_id', 'public'], name: 'public_post')]
#[Index(columns: ['published'], name: 'published')]
#[Index(columns: ['project_id'], name: 'project_id')]
#[Index(columns: ['import_service', 'import_id'], name: 'import_service_import_id')]
#[UniqueConstraint(name: 'project_id_url_id', columns: ['project_id', 'url_id'])]
#[Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[Column(name: 'id', type: Types::INTEGER)]
    #[Id]
    #[GeneratedValue(strategy: 'IDENTITY')]
    public int $id;

    #[ManyToOne(targetEntity: Project::class)]
    #[JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Project $project;

    #[Column(name: 'name', type: Types::STRING, length: 255, nullable: false)]
    protected string $name;

    #[Column(name: 'url_id', type: Types::STRING, length: 255, nullable: false)]
    public string $urlId;

    #[Column(name: 'meta_title', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaTitle = null;

    #[Column(name: 'meta_description', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaDescription = null;

    #[Column(name: 'meta_keywords', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $metaKeywords = null;

    #[Column(name: 'heading', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $heading = null;

    #[Column(name: 'subtitle', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $subtitle = null;

    #[Column(name: 'perex', type: Types::TEXT, length: 16383, nullable: true, options: ['default' => null])]
    protected ?string $perex = null;

    #[Column(name: 'content', type: Types::TEXT, length: 4294967295, nullable: true, options: ['default' => null])]
    protected ?string $content;

    #[Column(name: '`index`', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $index = null;

    #[Column(name: 'public', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $public = false;

    #[Column(name: 'lang', type: Types::STRING, length: 5, nullable: true, options: ['default' => null])]
    protected ?string $lang;

    #[Column(name: 'group_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $groupId = null;

    #[Column(name: 'published', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $published = null;

    #[Column(name: 'publish_date', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    protected ?DateTimeImmutable $publishDate = null;

    #[Column(name: 'created', type: Types::INTEGER)]
    protected int $created;

    #[Column(name: 'create_date', type: Types::DATETIME_IMMUTABLE)]
    protected DateTimeImmutable $createDate;

    #[Column(name: 'title', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $title = null;

    #[Column(name: 'import_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $importId = null;

    #[Column(name: 'import_service', type: Types::STRING, length: 50, nullable: true, options: ['default' => null])]
    protected ?string $importService = null;

    #[Column(name: 'infobox', type: Types::TEXT, length: 1024, nullable: true)]
    protected ?string $infoBox = null;

    #[Column(name: 'signature', type: Types::STRING, length: 1024, nullable: true, options: ['default' => null])]
    protected ?string $signature = null;

    #[Column(name: 'modified', type: Types::INTEGER, options: ['default' => 0])]
    protected int $modified = 0;

    #[Column(name: 'last_modified_date', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    protected ?DateTimeImmutable $lastModifiedDate = null;

    #[Column(name: 'publishers_id', type: Types::INTEGER, options: ['default' => 0])]
    protected int $publishersId = 0;

    #[Column(name: 'postsauthors_id', type: Types::INTEGER, options: ['default' => 0])]
    protected int $postsAuthorsId = 0;

    #[Column(name: 'gallery', type: Types::BOOLEAN, options: ['default' => false])]
    protected bool $gallery = false;

    #[Column(name: 'media_id', type: Types::INTEGER, options: ['default' => 0])]
    protected int $mediaId = 0;

    #[Column(name: 'authorsusers_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $authorsUsersId = null;

    #[Column(name: 'secret', type: Types::STRING, length: 10, nullable: true)]
    protected ?string $secret = null;

    #[Column(name: 'is_featured', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $isFeatured = false;

    #[Column(name: 'is_gallery_enabled', type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
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
        return $this->publishDate;
    }

    public function setPublishDate(?DateTimeImmutable $publishDate): void
    {
        $this->publishDate = $publishDate;
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

