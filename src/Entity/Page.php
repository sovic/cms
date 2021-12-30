<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\Page
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
    protected string $rawID;

    /**
     * @ORM\Column(name="head_title", type="string", length=200, nullable=true)
     */
    protected string $headTitle;

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
     * @ORM\Column(name="content_raw", length=65535, nullable=true)
     */
    protected string $contentRaw;

    /**
     * @ORM\Column(name="content_raw_utf", length=65535, nullable=true)
     */
    protected string $contentRawUtf;

    /**
     * @ORM\Column(name="lang", length=5, nullable=true, options={"default": "cs"})
     */
    protected string $lang = 'cs';

    /**
     * @ORM\Column(name="group_id", type="integer")
     */
    protected int $groupID;

    /**
     * @ORM\Column(name="toc", type="boolean")
     */
    protected bool $toc;

    /**
     * @ORM\Column(name="cta_link", length=255, nullable=true)
     */
    protected string $ctaLink;

    /**
     * @ORM\Column(name="cta_text", length=255, nullable=true)
     */
    protected string $ctaText;

    /**
     * @ORM\Column(name="content_type", length=255, nullable=true, options={"default"="NULL"})
     */
    protected ?string $contentType;

    /**
     * @ORM\Column(name="header", length=255, nullable=true, options={"default"="NULL"})
     */
    protected ?string $header;

    /**
     * @ORM\Column(name="theme", length=255, nullable=true, options={"default"="NULL"})
     */
    protected ?string $theme;

    /**
     * @ORM\Column(name="in_sitemap", type="boolean", nullable=false, options={"default": true})
     */
    protected bool $inSitemap = true;

    /**
     * @ORM\Column(name="last_update_date", type="datetime_immutable", nullable=true, options={"default": null})
     */
    protected ?DateTimeImmutable $lastUpdateDate;

    /**
     * @ORM\Column(name="side_menu_id", length=255, nullable=true, options={"default"="NULL"})
     */
    protected ?string $sideMenuId;

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

    public function getContentRaw(): string
    {
        return $this->contentRaw;
    }

    public function setContentRaw(string $contentRaw): void
    {
        $this->contentRaw = $contentRaw;
    }

    public function getContentRawUtf(): string
    {
        return $this->contentRawUtf;
    }

    public function setContentRawUtf(string $contentRawUtf): void
    {
        $this->contentRawUtf = $contentRawUtf;
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

    public function isToc(): bool
    {
        return $this->toc;
    }

    public function setToc(bool $toc): void
    {
        $this->toc = $toc;
    }

    public function getCtaLink(): string
    {
        return $this->ctaLink;
    }

    public function setCtaLink(string $ctaLink): void
    {
        $this->ctaLink = $ctaLink;
    }

    public function getCtaText(): string
    {
        return $this->ctaText;
    }

    public function setCtaText(string $ctaText): void
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

    public function setInSitemap($inSitemap): void
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
