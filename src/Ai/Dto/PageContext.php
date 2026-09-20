<?php

namespace Sovic\Cms\Ai\Dto;

use Sovic\Cms\Entity\Page;

final readonly class PageContext
{
    public function __construct(
        public string $name,
        public string $heading,
        public string $lang,
        public string $content,
    ) {
    }

    /**
     * @param string|null $currentContent unsaved editor content, takes precedence over the stored page content
     */
    public static function fromPage(Page $page, ?string $currentContent = null): self
    {
        return new self(
            (string) $page->getName(),
            (string) $page->getHeading(),
            $page->getLang() ?: 'cs',
            $currentContent ?? (string) $page->getContent(),
        );
    }
}
