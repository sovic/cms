<?php

namespace Sovic\Cms\Post;

use Sovic\Cms\Entity\Tag;
use Sovic\Common\Project\Project;

class PostSearchRequest
{
    public ?Project $project = null;
    public bool $includePrivate = false;
    public ?string $search = null;
    public ?Tag $tag = null;
    public ?string $author = null;
    public ?int $maxId = null;
}
