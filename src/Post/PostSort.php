<?php

namespace Sovic\Cms\Post;

use Sovic\Cms\Enum\SortInterface;
use SortDirection;

enum PostSort: string implements SortInterface
{
    // newest published first
    case PublishDate = 'publish_date';
    // latest created first
    case Latest = 'latest';

    public function getOrderBy(): array
    {
        return match ($this) {
            self::PublishDate => [
                'publishDate' => SortDirection::Descending,
                'id' => SortDirection::Descending,
            ],
            self::Latest => [
                'id' => SortDirection::Descending,
            ],
        };
    }
}
