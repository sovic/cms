<?php

namespace Sovic\Cms\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sovic\Cms\Repository\PageRepository;

#[ORM\Table(name: 'page')]
#[ORM\Index(columns: ['project_id'], name: 'project_id')]
#[ORM\UniqueConstraint(name: 'project_id_url_id', columns: ['project_id', 'url_id'])]
#[ORM\Entity(repositoryClass: PageRepository::class)]
class Page
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Project::class)]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Project $project;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 200, nullable: true)]
    protected string $name;

    #[ORM\Column(name: 'url_id', type: Types::STRING, length: 200, nullable: true)]
    protected string $urlId;

    #[ORM\Column(name: 'head_title', type: Types::STRING, length: 200, nullable: true)]
    protected ?string $metaTitle = null;

    #[ORM\Column(name: 'meta_description', type: Types::STRING, length: 200, nullable: true)]
    protected ?string $metaDescription = null;

    #[ORM\Column(name: 'meta_keywords', type: Types::STRING, length: 200, nullable: true)]
    protected ?string $metaKeywords = null;

    #[ORM\Column(name: 'heading', type: Types::STRING, length: 150, nullable: true)]
    protected ?string $heading = null;

    #[ORM\Column(name: 'perex', type: Types::TEXT, length: 16383, nullable: true, options: ['default' => null])]
    protected ?string $perex = null;

    #[ORM\Column(name: 'content', type: Types::TEXT, length: 4294967295, nullable: true, options: ['default' => null])]
    protected ?string $content = null;

    #[ORM\Column(name: 'sequence', type: Types::INTEGER, nullable: true)]
    protected int $sequence;

    #[ORM\Column(name: 'public', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $public = false;

    #[ORM\Column(name: 'lang', length: 5, nullable: true, options: ['default' => null])]
    protected ?string $lang = null;

    #[ORM\Column(name: 'group_id', type: Types::INTEGER, nullable: true, options: ['default' => null])]
    protected ?int $groupId = null;

    #[ORM\Column(name: 'toc', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    protected bool $toc = false;

    #[ORM\Column(name: 'cta_link', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $ctaLink = null;

    #[ORM\Column(name: 'cta_text', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $ctaText = null;

    #[ORM\Column(name: 'content_type', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $contentType = null;

    #[ORM\Column(name: 'header', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $header = null;

    #[ORM\Column(name: 'theme', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $theme = null;

    #[ORM\Column(name: 'in_sitemap', type: Types::BOOLEAN, nullable: false, options: ['default' => true])]
    protected bool $inSitemap = true;

    #[ORM\Column(name: 'last_update_date', type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['default' => null])]
    protected ?DateTimeImmutable $lastUpdateDate = null;

    #[ORM\Column(name: 'side_menu_id', type: Types::STRING, length: 255, nullable: true, options: ['default' => null])]
    protected ?string $sideMenuId = null;

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

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(?string $lang): void
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

    public function hasToc(): bool
    {
        return $this->toc;
    }

    public function setHasToc(bool $toc): void
    {
        $this->toc = $toc;
    }

    public function getCtaLink(): ?string
    {
        return $this->ctaLink;
    }

    public function setCtaLink(?string $ctaLink): void
    {
        $this->ctaLink = $ctaLink;
    }

    public function getCtaText(): ?string
    {
        return $this->ctaText;
    }

    public function setCtaText(?string $ctaText): void
    {
        $this->ctaText = $ctaText;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getHeader(): ?string
    {
        return $this->header;
    }

    public function setHeader(?string $header): void
    {
        $this->header = $header;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): void
    {
        $this->theme = $theme;
    }

    public function isInSitemap(): bool
    {
        return $this->inSitemap;
    }

    public function setInSitemap(bool $inSitemap): void
    {
        $this->inSitemap = $inSitemap;
    }

    public function getLastUpdateDate(): ?DateTimeImmutable
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateDate(?DateTimeImmutable $lastUpdateDate): void
    {
        $this->lastUpdateDate = $lastUpdateDate;
    }

    public function getSideMenuId(): ?string
    {
        return $this->sideMenuId;
    }

    public function setSideMenuId(?string $sideMenuId): void
    {
        $this->sideMenuId = $sideMenuId;
    }
}
