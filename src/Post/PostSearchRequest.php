<?php

namespace Sovic\Cms\Post;

use Sovic\Cms\Entity\Tag;
use Sovic\Common\DataList\AbstractSearchRequest;
use Sovic\Common\Project\Project;

/**
 * Visibility (VisibilityId) maps to post public flag:
 *  - Public: public posts only (default)
 *  - Private: non-public posts only
 *  - All: both
 */
class PostSearchRequest extends AbstractSearchRequest
{
    private ?Project $project = null;
    private ?Tag $tag = null;
    private ?int $maxId = null;

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): void
    {
        $this->project = $project;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): void
    {
        $this->tag = $tag;
    }

    /**
     * Only posts with id lower than maxId.
     */
    public function getMaxId(): ?int
    {
        return $this->maxId;
    }

    public function setMaxId(?int $maxId): void
    {
        $this->maxId = $maxId;
    }

    public function getSortOptions(): array
    {
        return array_column(PostSort::cases(), 'value');
    }

    public function getPostSort(): PostSort
    {
        return PostSort::tryFrom((string) $this->getSort()) ?? PostSort::PublishDate;
    }

    public function setPostSort(PostSort $sort): void
    {
        $this->setSort($sort->value);
    }
}
