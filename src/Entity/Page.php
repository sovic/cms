<?php

namespace SovicCms\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * SovicCms\Entity\Page
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity
 */
class Page
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @ORM\Column(name="name", type="string", length=200, nullable=true)
     */
    protected string $name;

    /**
     * @ORM\Column(name="raw_id", type="string", length=200, nullable=true)
     */
    protected string $urlId;

    /**
     * @ORM\Column(name="head_title", type="string", length=200, nullable=true)
     */
    protected ?string $metaTitle = null;

    /**
     * @ORM\Column(name="meta_description", type="string", length=200, nullable=true)
     */
    protected string $metaDescription;

    /**
     * @ORM\Column(name="meta_keywords", type="string", length=200, nullable=true)
     */
    protected string $metaKeywords;

    /**
     * @ORM\Column(name="heading", type="string", length=150, nullable=true)
     */
    protected string $heading;

    /**
     * @ORM\Column(name="perex", type="text", length=16383, nullable=true, options={"default"=NULL})
     */
    protected ?string $perex;

    /**
     * @ORM\Column(name="content", type="text", length=4294967295, nullable=true, options={"default"=NULL})
     */
    protected ?string $content;

    /**
     * @ORM\Column(name="sequence", type="integer", nullable=true)
     */
    protected int $sequence;

    /**
     * @ORM\Column(name="public", type="boolean", nullable=false, options={"default"=0})
     */
    protected bool $public = false;

    /**
     * @ORM\Column(name="lang", length=5, nullable=true, options={"default": NULL})
     */
    protected ?string $lang = null;

    /**
     * @ORM\Column(name="group_id", type="integer", nullable=true, options={"default": NULL})
     */
    protected ?int $groupId = null;

    /**
     * @ORM\Column(name="toc", type="boolean", nullable=false, options={"default"=0})
     */
    protected bool $toc = false;

    /**
     * @ORM\Column(name="cta_link", length=255, nullable=true, options={"default": NULL})
     */
    protected ?string $ctaLink = null;

    /**
     * @ORM\Column(name="cta_text", length=255, nullable=true, options={"default": NULL})
     */
    protected ?string $ctaText = null;

    /**
     * @ORM\Column(name="content_type", length=255, nullable=true, options={"default"=NULL})
     */
    protected ?string $contentType = null;

    /**
     * @ORM\Column(name="header", length=255, nullable=true, options={"default"=NULL})
     */
    protected ?string $header = null;

    /**
     * @ORM\Column(name="theme", length=255, nullable=true, options={"default"=NULL})
     */
    protected ?string $theme = null;

    /**
     * @ORM\Column(name="in_sitemap", type="boolean", nullable=false, options={"default": 1})
     */
    protected bool $inSitemap = true;

    /**
     * @ORM\Column(name="last_update_date", type="datetime_immutable", nullable=true, options={"default"=NULL})
     */
    protected ?DateTimeImmutable $lastUpdateDate = null;

    /**
     * @ORM\Column(name="side_menu_id", length=255, nullable=true, options={"default"=NULL})
     */
    protected ?string $sideMenuId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
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

    public function getMetaDescription(): string
    {
        return $this->metaDescription;
    }

    public function setMetaDescription(string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    public function getMetaKeywords(): string
    {
        return $this->metaKeywords;
    }

    public function setMetaKeywords(string $metaKeywords): void
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
